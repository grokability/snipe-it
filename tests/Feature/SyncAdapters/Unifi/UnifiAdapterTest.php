<?php

namespace Tests\Feature\SyncAdapters\Unifi;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\SyncAdapter;
use App\SyncAdapters\Unifi\UnifiAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the UniFi adapter through
 * SyncAdapter. Mocks the UniFi Network Integration API,
 * asserts assets + asset_external_sources land correctly, and that
 * the X-API-KEY header (not bearer, not session cookies) goes out on
 * the request.
 */
class UnifiAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_unifi_and_creates_assets()
    {
        $adapter = $this->configuredUnifiAdapter();

        Http::fake([
            '*/proxy/network/integration/v1/sites/*/devices*' => Http::response([
                'offset' => 0,
                'limit' => 100,
                'count' => 2,
                'totalCount' => 2,
                'data' => [
                    $this->unifiDevice(id: 'dev-1', name: 'core-switch-01', model: 'USW-Pro-24-PoE'),
                    $this->unifiDevice(id: 'dev-2', name: 'ap-01', model: 'U6-Pro'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'unifi', 'external_id' => 'dev-1']);
        $this->assertDatabaseHas('assets', ['name' => 'core-switch-01']);
    }

    public function test_x_api_key_header_is_sent_not_bearer_or_session()
    {
        $adapter = $this->configuredUnifiAdapter();

        Http::fake([
            '*/proxy/network/integration/v1/sites/*/devices*' => Http::response([
                'offset' => 0, 'limit' => 100, 'count' => 0, 'totalCount' => 0, 'data' => [],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        // UniFi's modern API uses X-API-KEY. A copy-paste regression
        // to withToken() (bearer) would fail auth against a real
        // controller. Also verifies no session-cookie auth leaks in.
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-KEY', 'fake-unifi-api-key')
                && ! $request->hasHeader('Authorization')
                && ! $request->hasHeader('Cookie');
        });
    }

    public function test_site_id_flows_into_the_request_path()
    {
        $adapter = $this->configuredUnifiAdapter(siteId: 'campus-b');

        Http::fake([
            '*' => Http::response([
                'offset' => 0, 'limit' => 100, 'count' => 0, 'totalCount' => 0, 'data' => [],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/proxy/network/integration/v1/sites/campus-b/devices');
        });
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredUnifiAdapter();

        Http::fake([
            '*/proxy/network/integration/v1/sites/*/devices*' => Http::response([
                'offset' => 0,
                'limit' => 100,
                'count' => 1,
                'totalCount' => 1,
                'data' => [
                    $this->unifiDevice(
                        id: 'dev-7',
                        name: 'building-a-gateway',
                        model: 'UDMPRO',
                        serialNumber: 'UBNT-XYZ',
                        macAddress: 'aa:bb:cc:11:22:33',
                        ipAddress: '10.0.0.1',
                        firmwareVersion: '3.2.7',
                        lastSeen: '2026-01-15T10:00:00.000Z',
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('unifi', $record->sourceKey);
        $this->assertSame('dev-7', $record->sourceId);
        $this->assertSame('building-a-gateway', $record->hostname);
        $this->assertSame('UDMPRO', $record->hardwareModel);
        $this->assertSame('UBNT-XYZ', $record->hardwareSerial);
        $this->assertSame('Ubiquiti', $record->manufacturer);
        $this->assertSame('aa:bb:cc:11:22:33', $record->primaryMac);
        $this->assertSame('10.0.0.1', $record->primaryIp);
        $this->assertSame('UniFi', $record->os);
        $this->assertSame('3.2.7', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_cloud_mode_pulls_devices_via_site_manager_endpoint()
    {
        // Base URL set to Site Manager (api.ui.com) flips the client to
        // cloud mode: devices come from /v1/devices?hostIds[]=... rather
        // than /proxy/network/integration/v1/sites/{id}/devices.
        // Regression test for the hosted-NAT gap called out by the
        // pennywise install (consoles behind NAT with Remote Access,
        // adapter had no cloud path).
        $adapter = $this->configuredUnifiAdapter(
            url: 'https://api.ui.com',
            siteId: 'host-abc-123',
        );

        Http::fake([
            'api.ui.com/v1/devices*' => Http::response([
                'httpStatusCode' => 200,
                'data' => [
                    [
                        'hostId' => 'host-abc-123',
                        'hostName' => 'UDM Pro Max',
                        'devices' => [
                            $this->siteManagerDevice(id: 'dev-cloud-1', name: 'ap-cloud-1', model: 'U6-Pro'),
                            $this->siteManagerDevice(id: 'dev-cloud-2', name: 'sw-cloud-1', model: 'USW-Lite-8-PoE'),
                        ],
                    ],
                ],
                'nextToken' => '',
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'unifi', 'external_id' => 'dev-cloud-1']);
        $this->assertDatabaseHas('assets', ['name' => 'ap-cloud-1']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.ui.com/v1/devices')
                && str_contains(urldecode($request->url()), 'hostIds[]=host-abc-123')
                && $request->hasHeader('X-API-KEY', 'fake-unifi-api-key');
        });
    }

    public function test_cloud_mode_translates_short_field_names_and_fills_missing_fields_null()
    {
        // Site Manager uses mac/ip/version short names and does not
        // expose serialNumber or lastSeen. Those gaps must map cleanly
        // onto the canonical HostInventoryRecord fields rather than
        // leaking the raw cloud payload shape through.
        $adapter = $this->configuredUnifiAdapter(
            url: 'https://api.ui.com',
            siteId: 'host-xyz',
        );

        Http::fake([
            'api.ui.com/v1/devices*' => Http::response([
                'httpStatusCode' => 200,
                'data' => [
                    [
                        'hostId' => 'host-xyz',
                        'devices' => [
                            [
                                'id' => 'dev-cloud-7',
                                'name' => 'gw-cloud',
                                'model' => 'UDM-SE',
                                'shortname' => 'UDMSE',
                                'mac' => 'aa:bb:cc:dd:ee:ff',
                                'ip' => '192.0.2.5',
                                'version' => '4.1.13',
                                'status' => 'online',
                                'isManaged' => true,
                            ],
                        ],
                    ],
                ],
                'nextToken' => '',
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('dev-cloud-7', $record->sourceId);
        $this->assertSame('gw-cloud', $record->hostname);
        $this->assertSame('UDM-SE', $record->hardwareModel);
        // Site Manager does not expose serial, so the adapter accepts
        // null rather than inventing a value.
        $this->assertNull($record->hardwareSerial);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('192.0.2.5', $record->primaryIp);
        $this->assertSame('4.1.13', $record->osVersion);
        $this->assertNull($record->lastSeen);
    }

    public function test_cloud_mode_fetch_sites_hits_v1_sites_and_uses_host_id_as_identifier()
    {
        // Refresh Sites in cloud mode walks /v1/sites and stores the
        // hostId as the dropdown identifier (device-list filter is
        // host-scoped on Site Manager, not site-scoped). The local
        // /proxy/network/integration/v1/sites path must not be hit.
        $instance = SyncAdapterInstance::where('slug', 'unifi')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://api.ui.com');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-unifi-api-key'));
        $adapter = new UnifiAdapter($instance->fresh());

        Http::fake([
            'api.ui.com/v1/sites*' => Http::response([
                'httpStatusCode' => 200,
                'data' => [
                    ['siteId' => 'site-1', 'hostId' => 'host-1', 'meta' => ['name' => 'Default']],
                    ['siteId' => 'site-2', 'hostId' => 'host-2', 'meta' => ['name' => 'Guest']],
                ],
                'nextToken' => '',
            ]),
        ]);

        $sites = $adapter->fetchSites();

        $this->assertCount(2, $sites);
        $this->assertSame(['id' => 'host-1', 'label' => 'Default'], $sites[0]);
        $this->assertSame(['id' => 'host-2', 'label' => 'Guest'], $sites[1]);

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/proxy/network/integration/v1/sites');
        });
    }

    private function configuredUnifiAdapter(
        string $siteId = 'default',
        string $url = 'https://unifi.example.test:8443',
    ): UnifiAdapter {
        $instance = SyncAdapterInstance::where('slug', 'unifi')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', $url);
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-unifi-api-key'));
        SyncAdapterConfig::put($instance->id, 'site_id', $siteId);

        return new UnifiAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function siteManagerDevice(
        string $id,
        string $name = 'device',
        string $model = 'U6-Pro',
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'model' => $model,
            'shortname' => strtoupper(str_replace('-', '', $model)),
            'mac' => 'aa:bb:cc:dd:ee:ff',
            'ip' => '192.0.2.10',
            'version' => '4.1.13',
            'status' => 'online',
            'isManaged' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function unifiDevice(
        string $id,
        string $name = 'device',
        string $model = 'U6-Pro',
        ?string $serialNumber = null,
        ?string $macAddress = null,
        ?string $ipAddress = null,
        ?string $firmwareVersion = null,
        ?string $lastSeen = null,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'model' => $model,
            'modelKey' => strtolower($model),
            'serialNumber' => $serialNumber,
            'macAddress' => $macAddress,
            'ipAddress' => $ipAddress,
            'firmwareVersion' => $firmwareVersion,
            'lastSeen' => $lastSeen,
            'state' => 'ONLINE',
            'adopted' => true,
            'uplink' => ['mac' => null],
        ];
    }
}
