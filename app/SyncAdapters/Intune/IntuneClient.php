<?php

namespace App\SyncAdapters\Intune;

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
 * The returned bearer is valid for ~60 minutes. A single sync run
 * stays inside that window so we don't refresh mid-run.
 *
 * Sovereign clouds swap both hosts. The `graphBaseUrl` and
 * `loginBaseUrl` constructor args let the adapter derive the login
 * host from the Graph host so the settings-page URL field controls
 * both endpoints.
 */
class IntuneClient
{
    private ?string $token = null;

    public function __construct(
        private readonly string $graphBaseUrl,
        private readonly string $loginBaseUrl,
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every managed device visible to the app registration.
     * Uses Graph's `@odata.nextLink` for cursor pagination. The
     * cursor URL is absolute and includes the base host, so we hand
     * it straight to Http::get() rather than composing against
     * $graphBaseUrl a second time.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function managedDevices(): iterable
    {
        $url = rtrim($this->graphBaseUrl, '/').'/v1.0/deviceManagement/managedDevices';

        do {
            $response = $this->request()->get($url)->throw()->json();

            foreach (($response['value'] ?? []) as $device) {
                yield $device;
            }

            $url = $response['@odata.nextLink'] ?? null;
        } while ($url !== null);
    }

    /**
     * Fetch (or reuse) a bearer token via OAuth 2.0 client-credentials
     * against the tenant-scoped token endpoint. Token is cached on
     * this instance for the life of the sync run.
     */
    private function bearer(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $tokenUrl = rtrim($this->loginBaseUrl, '/').'/'.$this->tenantId.'/oauth2/v2.0/token';

        $response = Http::asForm()
            ->timeout(30)
            ->post($tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => rtrim($this->graphBaseUrl, '/').'/.default',
            ])
            ->throw()
            ->json();

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
            ->asJson()
            ->acceptJson()
            ->timeout(30)
            ->patch($url, $payload)
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->bearer())
            ->acceptJson()
            ->timeout(30);
    }
}
