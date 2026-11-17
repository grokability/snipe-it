<?php

namespace App\SyncAdapters\Intune;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around Microsoft Graph for the Intune Managed Devices
 * endpoint. Handles OAuth 2.0 client-credentials token exchange and
 * Graph cursor pagination (via the `@odata.nextLink` response field).
 *
 * Auth model: Azure app registration with a tenant + client_id +
 * client_secret. Token exchange POSTs to the tenant-scoped OAuth 2.0
 * token endpoint with scope `https://graph.microsoft.com/.default`.
 * The returned bearer is valid for ~60 minutes. Pulls that iterate
 * thousands of devices + per-device detail fetches outlast that
 * window, so the bearer is proactively refreshed via expires_in once
 * it enters the TOKEN_REFRESH_WINDOW_SECONDS runway before expiry.
 *
 * Sovereign clouds swap both hosts. The `graphBaseUrl` and
 * `loginBaseUrl` constructor args let the adapter derive the login
 * host from the Graph host so the settings-page URL field controls
 * both endpoints.
 *
 * Graph API version: the unfiltered device pull uses `/v1.0/`, the
 * stable surface. Two call sites fall over to `/beta/` because the
 * stable version does not expose the data they need: the
 * composed-notes push on `updateManagedDevice()` (notes is a
 * beta-only writable field), and the entire scope-tag filter flow
 * (roleScopeTags resource and the roleScopeTagIds filter on
 * managedDevices). Microsoft does not guarantee beta stability, so a
 * Graph side change can break those paths without warning.
 */
class IntuneClient
{
    use RetriesTransientHttpErrors;

    /**
     * Refresh the bearer when it has fewer than this many seconds of
     * validity left. 60s is enough to cover the HTTP request round-trip
     * plus any retry backoff on the next Graph call, while staying tight
     * enough that we only re-fetch when there is a real risk of expiry.
     */
    private const TOKEN_REFRESH_WINDOW_SECONDS = 60;

    private ?string $token = null;

    private ?int $tokenExpiresAt = null;

    public function __construct(
        private readonly string $graphBaseUrl,
        private readonly string $loginBaseUrl,
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every managed device visible to the app registration.
     * Uses Graph's `@odata.nextLink` for cursor pagination.
     *
     * Server-side scope-tag filtering (via `$filter=roleScopeTagIds/any(...)`)
     * was tried first but Microsoft rejects the clause on both v1.0
     * and beta managedDevices endpoints (BadRequest, see #19849).
     * Scope-tag filtering is now applied client-side by the adapter
     * via per-device beta detail fetches, so this method stays on the
     * stable list surface and returns every device.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function managedDevices(): iterable
    {
        $graphOrigin = $this->origin($this->graphBaseUrl);
        $url = rtrim($this->graphBaseUrl, '/').'/v1.0/deviceManagement/managedDevices';

        do {
            // Pass no second argument. Laravel forwards it as Guzzle's
            // `query` option, which replaces the URL's query string even
            // when empty, stripping the $skiptoken off `@odata.nextLink`.
            // Tenants with more than one page would loop on page 1 forever.
            // See #19916.
            $response = $this->request()->get($url)->throw()->json();

            foreach (($response['value'] ?? []) as $device) {
                yield $device;
            }

            $url = $this->rebasedCursor($response['@odata.nextLink'] ?? null, $graphOrigin);
        } while ($url !== null);
    }

    /**
     * Fetch every role scope tag defined in the tenant. Used by the
     * adapter to resolve admin-entered scope tag names into the IDs
     * the managedDevices filter needs, and (eventually) to populate
     * a settings-page multiselect of available tags.
     *
     * Returns an array of ['id' => '...', 'displayName' => '...'].
     *
     * @return array<int, array{id: string, displayName: string}>
     */
    public function roleScopeTags(): array
    {
        $graphOrigin = $this->origin($this->graphBaseUrl);
        // The roleScopeTag resource is only exposed on /beta/. The
        // stable v1.0 surface returns a 400 "Resource not found for
        // the segment 'roleScopeTags'" so there is no fallback.
        $url = rtrim($this->graphBaseUrl, '/').'/beta/deviceManagement/roleScopeTags';

        $out = [];
        do {
            $response = $this->request()->get($url)->throw()->json();
            foreach (($response['value'] ?? []) as $tag) {
                $id = $tag['id'] ?? null;
                $name = $tag['displayName'] ?? null;
                if (is_scalar($id) && is_string($name)) {
                    $out[] = ['id' => (string) $id, 'displayName' => $name];
                }
            }
            $url = $this->rebasedCursor($response['@odata.nextLink'] ?? null, $graphOrigin);
        } while ($url !== null);

        return $out;
    }

    /**
     * Strip a server-supplied absolute URL down to path+query and
     * re-base it onto the configured graph origin, so a hostile
     * upstream can't redirect the cursor (and its bearer) to an
     * attacker-chosen target. Returns null when the cursor is
     * absent or unparseable, or when the configured graph URL
     * itself has no usable origin.
     */
    private function rebasedCursor(?string $url, ?string $origin): ?string
    {
        if ($url === null || $url === '' || $origin === null) {
            return null;
        }

        $parsed = parse_url($url);
        if (! is_array($parsed)) {
            return null;
        }

        // Reject path-relative cursors with no leading slash
        // ("page2" rather than "/page2"). Rebasing those produced an
        // unparseable "https://hostpage2" that aborted the sync on the
        // next iteration. Fails safe but we'd rather be explicit
        // (reported post-fix by Zer0Gate as a robustness note).
        // Only path+query is kept from the supplied cursor on purpose,
        // so any path prefix on the configured base URL (not Graph's
        // case, but legal in general) is dropped. Absolute cursors are
        // the standard shape here and this matches that.
        $path = $parsed['path'] ?? '/';
        if (! str_starts_with($path, '/')) {
            return null;
        }

        return $origin.$path.(isset($parsed['query']) ? '?'.$parsed['query'] : '');
    }

    /**
     * Scheme+host(+port) of the given absolute URL, or null when it is
     * not a parseable absolute URL.
     */
    private function origin(string $url): ?string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /**
     * Per-device GET against Graph's managedDevice endpoint with a
     * caller-chosen $select set. Used for properties the LIST
     * endpoint always returns null for (ethernetMacAddress,
     * physicalMemoryInBytes, etc. - Microsoft docs explicitly flag
     * these as "requires per-device GET"), and for roleScopeTagIds,
     * which the list surface also returns empty regardless of $select
     * (see #19849). Returns the attributes array on success. Fails
     * soft to null on 4xx/5xx so one bad device doesn't abort the
     * enclosing enrichment pass.
     *
     * $apiVersion is passed through so callers can hit `beta` for
     * fields that only populate there (roleScopeTagIds). Default
     * stays on v1.0 for the existing enrichment call sites.
     *
     * @param  array<int, string>  $select
     * @return array<string, mixed>|null
     */
    public function managedDeviceDetail(string $deviceId, array $select, string $apiVersion = 'v1.0'): ?array
    {
        $url = rtrim($this->graphBaseUrl, '/').'/'.$apiVersion.'/deviceManagement/managedDevices/'.rawurlencode($deviceId);

        try {
            return $this->request()
                ->get($url, ['$select' => implode(',', $select)])
                ->throw()
                ->json();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Fetch (or reuse) a bearer token via OAuth 2.0 client-credentials
     * against the tenant-scoped token endpoint. The token is cached on
     * this instance across requests. When the cached token has fewer
     * than TOKEN_REFRESH_WINDOW_SECONDS of validity left, re-exchange
     * so a long-running pull (thousands of devices + per-device detail
     * calls, routinely 60+ minutes) doesn't trip Microsoft's
     * "InvalidAuthenticationToken: Invalid token lifetime" error part
     * way through.
     */
    private function bearer(): string
    {
        if ($this->token !== null && $this->tokenExpiresAt !== null
            && $this->tokenExpiresAt - time() > self::TOKEN_REFRESH_WINDOW_SECONDS) {
            return $this->token;
        }

        $tokenUrl = rtrim($this->loginBaseUrl, '/').'/'.$this->tenantId.'/oauth2/v2.0/token';

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post($tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => rtrim($this->graphBaseUrl, '/').'/.default',
            ])
            ->throw()
            ->json();

        // expires_in is seconds from issue (RFC 6749). Fall back to a
        // conservative 55 minutes if the server omits it, matching the
        // old implicit assumption but still letting the refresh window
        // kick in before the real expiry.
        $expiresIn = (int) ($response['expires_in'] ?? 3300);
        $this->tokenExpiresAt = time() + $expiresIn;

        return $this->token = (string) ($response['access_token'] ?? '');
    }

    /**
     * Push writable metadata to a managed device via Graph beta's
     * per-device endpoint. The v1.0 endpoint doesn't expose notes
     * as writable. Beta does (matches what community integrations
     * like Brady Widener's Snipe-IT-Azure-Integration use). Fields
     * the caller doesn't include stay untouched.
     *
     * @param  array<string, scalar|null>  $payload
     */
    public function updateManagedDevice(string $deviceId, array $payload): void
    {
        $url = rtrim($this->graphBaseUrl, '/').'/beta/deviceManagement/managedDevices/'.$deviceId;

        Http::withToken($this->bearer())
            ->withOptions(['allow_redirects' => false])
            ->asJson()
            ->acceptJson()
            ->timeout(30)
            ->patch($url, $payload)
            ->throw();
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::withToken($this->bearer())
                ->withOptions(['allow_redirects' => false])
                ->acceptJson()
                ->timeout(30)
        );
    }
}
