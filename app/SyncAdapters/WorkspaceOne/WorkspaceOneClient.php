<?php

namespace App\SyncAdapters\WorkspaceOne;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Omnissa Workspace ONE UEM REST API.
 * Handles OAuth 2.0 client-credentials token exchange against the
 * region-scoped auth host and page-based iteration through the
 * device search endpoint.
 *
 * Auth model: client_id + client_secret from a Workspace ONE OAuth
 * application, exchanged at the region-appropriate token endpoint.
 * The `aw-tenant-code` header identifies which Workspace ONE tenant
 * the call is scoped to. The same value can be used across every
 * request in a sync run.
 *
 * Pagination: /api/mdm/devices/search returns { Total, PageSize,
 * Page, Devices[] }. Page is zero-indexed. Walk pages until the
 * running count reaches Total (or the page comes back empty).
 */
class WorkspaceOneClient
{
    private ?string $token = null;

    public function __construct(
        private readonly string $apiBaseUrl,
        private readonly string $authBaseUrl,
        private readonly string $tenantCode,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every device visible to the OAuth application.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function devices(int $pageSize = 500): iterable
    {
        $page = 0;
        $seen = 0;

        do {
            $response = $this->request()
                ->get('/api/mdm/devices/search', [
                    'pagesize' => $pageSize,
                    'page' => $page,
                ])
                ->throw()
                ->json();

            $devices = $response['Devices'] ?? [];
            foreach ($devices as $device) {
                yield $device;
            }

            $seen += count($devices);
            $total = $response['Total'] ?? null;
            $done = count($devices) === 0
                || ($total !== null && $seen >= $total);
            $page++;
        } while (! $done);
    }

    /**
     * Fetch (or reuse) a bearer token via OAuth 2.0 client-credentials
     * against the region-scoped auth host.
     */
    private function bearer(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post(rtrim($this->authBaseUrl, '/').'/connect/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ])
            ->throw()
            ->json();

        return $this->token = (string) ($response['access_token'] ?? '');
    }

    /**
     * Update a device's writable metadata via Workspace ONE's device
     * update endpoint. AssetNumber is WS1's asset_tag equivalent, a
     * top-level string field on the device record.
     *
     * @param  array<string, scalar|null>  $payload
     */
    public function updateDevice(string $deviceUuid, array $payload): void
    {
        $this->request()
            ->asJson()
            ->put('/api/mdm/devices/'.$deviceUuid, $payload)
            ->throw();
    }

    /**
     * Set a Custom Attribute value on a device. WS1 Custom Attributes
     * are separately-defined key-value pairs the admin creates under
     * Devices -> Profiles & Resources -> Custom Attributes. Writing
     * uses a different endpoint from device-level fields (`AssetNumber`
     * uses updateDevice above). The `Application` / `AttributeName` /
     * `Value` shape is what WS1's REST API expects for CA writes.
     */
    public function updateDeviceCustomAttribute(string $deviceUuid, string $attributeName, string $value): void
    {
        $this->request()
            ->asJson()
            ->post('/api/mdm/devices/'.$deviceUuid.'/customattributes', [
                'CustomAttributes' => [
                    [
                        'Name' => $attributeName,
                        'Value' => $value,
                        'Application' => 'com.snipeit.sync',
                    ],
                ],
            ])
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->apiBaseUrl, '/'))
            ->withToken($this->bearer())
            ->withHeaders(['aw-tenant-code' => $this->tenantCode])
            ->acceptJson()
            ->timeout(30);
    }
}
