<?php

namespace App\SyncAdapters\AppleBusinessManager;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Thin wrapper around Apple Business Manager (ABM) and Apple School
 * Manager (ASM) REST APIs. Both products share the same endpoint shape
 * and JWT client-assertion auth model. Only the base host differs
 * (api-business.apple.com vs api-school.apple.com). The mode toggle in
 * the adapter's credential schema picks which host + scope to use.
 *
 * Auth model: Apple issues an OAuth application (Settings > API in the
 * ABM/ASM console) as a Client ID + Key ID + downloaded EC P-256
 * private key (.pem). This client signs a JWT client-assertion with
 * ES256, exchanges it at account.apple.com for a short-lived bearer,
 * and caches that bearer for the life of the sync run.
 *
 * Pagination: /v1/orgDevices uses `limit` + `cursor` for forward
 * paging. `Meta.Paging.Total` gives the total record count when the
 * caller wants to size progress reporting.
 */
class AppleBusinessManagerClient
{
    use RetriesTransientHttpErrors;

    private const APPLE_AUDIENCE = 'https://account.apple.com/auth/oauth2/v2/token';

    private const APPLE_TOKEN_ENDPOINT = 'https://account.apple.com/auth/oauth2/v2/token';

    private ?string $accessToken = null;

    /**
     * @param  'business'|'school'  $mode
     */
    public function __construct(
        private readonly string $clientId,
        private readonly string $keyId,
        private readonly string $privateKeyPem,
        private readonly string $mode = 'business',
    ) {}

    /**
     * Iterate every device in the org. Devices come back with an
     * `Attributes` object carrying SerialNumber, PartNumber,
     * ProductFamily, Color, OrderNumber, OrderDateTime, and other
     * pre-provisioning fields. Hostnames / OS versions / last-seen
     * are NOT here (ABM is a purchase-registration portal, not a
     * runtime inventory), so downstream mapping leaves those null.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function devices(int $pageSize = 100): iterable
    {
        $cursor = null;

        do {
            $params = ['limit' => $pageSize];
            if (is_string($cursor)) {
                $params['cursor'] = $cursor;
            }

            $response = $this->request()
                ->get('/v1/orgDevices', $params)
                ->throw()
                ->json();

            $devices = $response['data'] ?? [];
            foreach ($devices as $device) {
                yield $device;
            }

            $cursor = $response['meta']['paging']['nextCursor'] ?? null;
        } while (is_string($cursor) && $cursor !== '');
    }

    /**
     * Fetch a single device by its AxM id. The list endpoint drops
     * released / sold / returned devices from its results, but this
     * one keeps returning them with releasedFromOrgDateTime set and
     * attributes.status === 'DEVICE_ASSIGNMENT_UNKNOWN'. Used by the
     * auto-archive confirmation hook to distinguish a
     * genuinely released device from one that fell out of pagination
     * for a transient vendor reason. Returns null on 404.
     *
     * @return array<string, mixed>|null
     */
    public function deviceById(string $deviceId): ?array
    {
        $response = $this->request()
            ->get('/v1/orgDevices/' . $deviceId);

        if ($response->status() === 404) {
            return null;
        }

        $response->throw();

        return $response->json('data');
    }

    /**
     * Fetch every AppleCare coverage plan for a single device. Apple
     * returns an array (a device can carry multiple overlapping plans
     * for various renewals / extensions), so callers pick the best
     * one for their purposes. Each entry has an attributes object
     * with agreementNumber, startDateTime, endDateTime, status,
     * paymentType, isCanceled, isRenewable, description.
     *
     * @return array<int, array<string, mixed>>
     */
    public function deviceAppleCareCoverage(string $deviceId): array
    {
        $response = $this->request()
            ->get('/v1/orgDevices/'.$deviceId.'/appleCareCoverage')
            ->throw()
            ->json();

        return $response['data'] ?? [];
    }

    /**
     * Fetch Activation Lock status for one device. Apple returns a
     * single Device-shaped object with an attributes.activationLockEnabled
     * boolean. Handy at asset-checkin time since Activation Lock has
     * to be cleared before a device can be redeployed to a new user.
     *
     * @return array<string, mixed>
     */
    public function deviceActivationLockStatus(string $deviceId): array
    {
        $response = $this->request()
            ->get('/v1/orgDevices/'.$deviceId.'/activationLockStatus')
            ->throw()
            ->json();

        return $response['data'] ?? [];
    }

    /**
     * List every device enrolled in Apple Business Manager's built-in
     * MDM (formerly Apple Business Essentials, now folded into AxM).
     * Cheap: one call per sync run. The id on each entry IS the
     * device's serial number, so the adapter joins straight to
     * orgDevices by serial. Records that don't appear here just
     * aren't enrolled in the built-in MDM and stay un-enriched.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function mdmDevices(int $pageSize = 100): iterable
    {
        $cursor = null;

        do {
            $params = ['limit' => $pageSize];
            if (is_string($cursor)) {
                $params['cursor'] = $cursor;
            }

            $response = $this->request()
                ->get('/v1/mdmDevices', $params)
                ->throw()
                ->json();

            foreach ($response['data'] ?? [] as $device) {
                yield $device;
            }

            $cursor = $response['meta']['paging']['nextCursor'] ?? null;
        } while (is_string($cursor) && $cursor !== '');
    }

    /**
     * Fetch runtime detail for one MDM-enrolled device: osVersion,
     * lastCheckInDateTime, MAC addresses, storage, security posture
     * (FileVault, firewall, lock/lost/erase statuses). One call per
     * device, so gated behind hasMappedMdmDeviceDetailFields() at
     * the adapter side.
     *
     * @return array<string, mixed>
     */
    public function mdmDeviceDetails(string $serial): array
    {
        $response = $this->request()
            ->get('/v1/mdmDevices/'.$serial.'/details')
            ->throw()
            ->json();

        return $response['data'] ?? [];
    }

    /**
     * Build a device-id -> MDM server name map by walking every MDM
     * server's device linkages. Devices unassigned to any MDM server
     * simply aren't in the returned map, so a `?? ''` fallback at the
     * adapter side keeps normalize() clean. Called once per sync run.
     *
     * @return array<string, string>
     */
    public function deviceToMdmServerMap(): array
    {
        $servers = $this->request()
            ->get('/v1/mdmServers')
            ->throw()
            ->json();

        $serverNames = [];
        foreach ($servers['data'] ?? [] as $server) {
            $id = $server['id'] ?? null;
            $name = $server['attributes']['serverName'] ?? null;
            if (is_string($id) && is_string($name)) {
                $serverNames[$id] = $name;
            }
        }

        $deviceToServer = [];
        foreach ($serverNames as $serverId => $serverName) {
            $cursor = null;
            do {
                $params = ['limit' => 1000];
                if (is_string($cursor)) {
                    $params['cursor'] = $cursor;
                }
                $response = $this->request()
                    ->get('/v1/mdmServers/'.$serverId.'/relationships/devices', $params)
                    ->throw()
                    ->json();

                foreach ($response['data'] ?? [] as $linkage) {
                    $deviceId = $linkage['id'] ?? null;
                    if (is_string($deviceId)) {
                        $deviceToServer[$deviceId] = $serverName;
                    }
                }

                $cursor = $response['meta']['paging']['nextCursor'] ?? null;
            } while (is_string($cursor) && $cursor !== '');
        }

        return $deviceToServer;
    }

    /**
     * Base HTTP client with resolved bearer token. On 429 the sleep
     * callback honors Apple's `Retry-After` header (capped at 2
     * minutes so a very long server-side cooldown doesn't stall the
     * sync beyond user patience). Falls back to exponential backoff
     * when Retry-After is missing from the API response.
     *
     * The N+1 per-device enrichment calls (AppleCare, Activation Lock,
     * MDM details) are still the biggest cost on large pulls - admins
     * who hit 429 even with this retry logic can trim mappings that
     * drive those extra per-device fetches.
     */
    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl($this->apiBaseUrl())
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->bearer())
                ->acceptJson()
                ->timeout(30)
        );
    }

    /**
     * Exchange a signed JWT client-assertion for a bearer token.
     * Tokens are short-lived (~1 hour) and cached on this instance so
     * a full sync doesn't retrigger auth per request. Failures throw
     * up to the sync runner and land in the sync-adapters log.
     */
    private function bearer(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $assertion = $this->buildClientAssertion();

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post(self::APPLE_TOKEN_ENDPOINT, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
                'scope' => $this->tokenScope(),
            ])
            ->throw()
            ->json();

        return $this->accessToken = (string) ($response['access_token'] ?? '');
    }

    /**
     * Sign an ES256 JWT client-assertion. Apple requires 180-day max
     * expiration. We use 5 minutes, which is more than enough for the
     * single token exchange this triggers. jti keeps replay windows
     * closed even under a rotating fleet of sync processes.
     */
    private function buildClientAssertion(): string
    {
        $now = time();
        $payload = [
            'sub' => $this->clientId,
            'aud' => self::APPLE_AUDIENCE,
            'iss' => $this->clientId,
            'iat' => $now,
            'exp' => $now + 300,
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, $this->privateKeyPem, 'ES256', $this->keyId);
    }

    private function apiBaseUrl(): string
    {
        return $this->mode === 'school'
            ? 'https://api-school.apple.com'
            : 'https://api-business.apple.com';
    }

    private function tokenScope(): string
    {
        return $this->mode === 'school' ? 'school.api' : 'business.api';
    }
}
