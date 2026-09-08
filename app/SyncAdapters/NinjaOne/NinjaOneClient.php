<?php

namespace App\SyncAdapters\NinjaOne;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the NinjaOne (formerly NinjaRMM) REST API v2.
 * Handles OAuth 2.0 client-credentials token exchange against the
 * region-scoped token endpoint and cursor-based pagination through
 * the /v2/devices endpoint.
 *
 * Auth model: OAuth application (client_id + client_secret) issued
 * from Administration -> Apps -> API in the NinjaOne dashboard.
 * Token exchange POSTs to /ws/oauth/token with scope=monitoring.
 * The returned bearer is valid for ~60 minutes and is cached on
 * this instance for the life of the sync run.
 *
 * Pagination: /v2/devices returns a flat array. Cursor forward using
 * `?after=<lastId>&pageSize=N` until a short page comes back.
 */
class NinjaOneClient
{
    private ?string $token = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every device visible to the OAuth application. Uses
     * the detailed device shape (`?df=os,references`) so we get the
     * nested os object + org/location references in one call rather
     * than hydrating per-device on the client side.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function devices(int $pageSize = 500): iterable
    {
        $after = 0;

        do {
            $devices = $this->request()
                ->get('/v2/devices-detailed', [
                    'pageSize' => $pageSize,
                    'after' => $after,
                ])
                ->throw()
                ->json();

            $devices = is_array($devices) ? $devices : [];

            foreach ($devices as $device) {
                yield $device;
            }

            $count = count($devices);
            if ($count > 0) {
                $after = (int) ($devices[$count - 1]['id'] ?? 0);
            }
        } while ($count === $pageSize && $after > 0);
    }

    /**
     * Fetch (or reuse) a bearer token via OAuth 2.0 client-credentials.
     * The scope=monitoring value is the minimum needed for read access
     * to the devices endpoint. Broader scopes (management, control)
     * aren't required because sync is pull-only.
     */
    private function bearer(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post(rtrim($this->baseUrl, '/').'/ws/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'monitoring',
            ])
            ->throw()
            ->json();

        return $this->token = (string) ($response['access_token'] ?? '');
    }

    /**
     * Update a device's custom field values. NinjaOne exposes no
     * first-class asset_tag field, but admins can create their own
     * "custom fields" per-device and write to them via
     * PATCH /v2/device/{id}/custom-fields. Body is a flat map of
     * custom-field-name -> value. NinjaOne rejects names that don't
     * exist on the device role's custom-field schema.
     *
     * @param  array<string, scalar|null>  $fields
     */
    public function updateDeviceCustomFields(string $deviceId, array $fields): void
    {
        $this->request()
            ->asJson()
            ->patch('/v2/device/'.$deviceId.'/custom-fields', $fields)
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->bearer())
            ->acceptJson()
            ->timeout(30);
    }
}
