<?php

namespace App\SyncAdapters\SophosCentral;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around the Sophos Central API. Owns auth, tenant
 * discovery and pagination for the endpoints the sync adapter uses.
 * Yields raw decoded JSON, no normalization (that's the adapter's job).
 *
 * Auth model: OAuth 2.0 client credentials. The admin creates an API
 * credential in Sophos Central (Global Settings -> API Credentials
 * Management) with the Service Principal Read-Only role, and copies the
 * client id + client secret into this adapter's settings. This client
 * exchanges those for a bearer token at id.sophos.com (valid for about
 * an hour) and caches it on the instance for the rest of the run.
 *
 * Discovery: Sophos serves each tenant from a regional data host. After
 * the token exchange, GET /whoami/v1 on the global API host returns the
 * tenant id and that data host. Every data call then goes to the data
 * host with the bearer token and an X-Tenant-ID header. Only tenant-level
 * credentials are supported here; partner and organization credentials
 * return a different idType and would need a tenant picker.
 *
 * Sophos Central API reference: https://developer.sophos.com/intro
 */
class SophosCentralClient
{
    public const TOKEN_URL = 'https://id.sophos.com/api/v2/oauth2/token';

    public const WHOAMI_URL = 'https://api.central.sophos.com/whoami/v1';

    /** Guard against a paging loop if the API ever repeats a key. */
    private const MAX_PAGES = 1000;

    private ?string $accessToken = null;

    private ?string $tenantId = null;

    private ?string $dataHost = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every endpoint (computers and servers) in the tenant, one
     * page at a time. `view=full` is required: the default, basic and
     * summary views leave serialNumber out.
     *
     * Paging is key-based: each response carries pages.nextKey, which is
     * passed back as pageFromKey. The loop ends when no nextKey comes back.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function endpoints(int $pageSize = 500): iterable
    {
        $fromKey = null;
        $seenKeys = [];

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $params = ['pageSize' => $pageSize, 'view' => 'full'];
            if (is_string($fromKey)) {
                $params['pageFromKey'] = $fromKey;
            }

            $response = $this->request()
                ->get('/endpoint/v1/endpoints', $params)
                ->throw()
                ->json();

            foreach ($response['items'] ?? [] as $endpoint) {
                yield $endpoint;
            }

            $fromKey = $response['pages']['nextKey'] ?? null;
            if (! is_string($fromKey) || $fromKey === '' || isset($seenKeys[$fromKey])) {
                return;
            }
            $seenKeys[$fromKey] = true;
        }
    }

    /**
     * Base HTTP client for tenant-scoped calls: the discovered data host,
     * the bearer token and the tenant header. Retries connection errors
     * and 429s the same way the Apple Business Manager client does.
     */
    private function request(): PendingRequest
    {
        $this->discover();

        return Http::baseUrl($this->dataHost)
            ->withOptions(['allow_redirects' => false])
            ->withToken($this->bearer())
            ->withHeaders(['X-Tenant-ID' => $this->tenantId])
            ->acceptJson()
            ->timeout(60)
            ->retry(3, 500, fn (\Exception $e) => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->status() === 429));
    }

    /**
     * Resolve the tenant id and regional data host once per run.
     * The data host comes from the API rather than from the admin, so it
     * skips the base-URL validation other adapters get; check it here
     * before any token is sent to it.
     */
    private function discover(): void
    {
        if ($this->tenantId !== null && $this->dataHost !== null) {
            return;
        }

        $whoami = Http::withOptions(['allow_redirects' => false])
            ->withToken($this->bearer())
            ->acceptJson()
            ->timeout(30)
            ->get(self::WHOAMI_URL)
            ->throw()
            ->json();

        $idType = (string) ($whoami['idType'] ?? '');
        if ($idType !== 'tenant') {
            throw new RuntimeException(
                'This Sophos Central credential is '.($idType !== '' ? $idType : 'not')
                .'-level. Create a tenant-level API credential (Global Settings -> API Credentials Management) and use that instead.',
            );
        }

        $tenantId = (string) ($whoami['id'] ?? '');
        $dataHost = (string) ($whoami['apiHosts']['dataRegion'] ?? '');
        if ($tenantId === '' || ! self::isSophosHost($dataHost)) {
            throw new RuntimeException('Sophos Central returned an unexpected tenant id or data host from whoami.');
        }

        $this->tenantId = $tenantId;
        $this->dataHost = rtrim($dataHost, '/');
    }

    /**
     * True for an https URL whose host is sophos.com or a subdomain of it.
     */
    public static function isSophosHost(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }
        $host = strtolower($parts['host'] ?? '');

        return $host === 'sophos.com' || str_ends_with($host, '.sophos.com');
    }

    /**
     * Exchange the client credentials for a bearer token, cached for the
     * rest of this run. A full pull finishes well inside the token's hour.
     */
    private function bearer(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'token',
            ])
            ->throw()
            ->json();

        $token = (string) ($response['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('Sophos Central did not return an access token. Check the client id and secret.');
        }

        return $this->accessToken = $token;
    }
}
