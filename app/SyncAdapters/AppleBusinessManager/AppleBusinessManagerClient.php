<?php

namespace App\SyncAdapters\AppleBusinessManager;

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

            $cursor = $response['links']['next'] ?? null;
        } while (is_string($cursor) && $cursor !== '');
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

                $cursor = $response['links']['next'] ?? null;
            } while (is_string($cursor) && $cursor !== '');
        }

        return $deviceToServer;
    }

    /**
     * Base HTTP client with resolved bearer token. Retries on 429 with
     * exponential backoff. Apple's rate limit is generous but bursts
     * during a full org pull can trip it briefly.
     */
    private function request(): PendingRequest
    {
        return Http::baseUrl($this->apiBaseUrl())
            ->withToken($this->bearer())
            ->acceptJson()
            ->timeout(30)
            ->retry(3, 500, fn (\Exception $e) => $e instanceof \Illuminate\Http\Client\ConnectionException
                || ($e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429));
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
