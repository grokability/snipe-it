<?php

namespace Tests\Feature\SyncAdapters\Unifi;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\SyncHostFromAdapter;
use App\SyncAdapters\Unifi\UnifiAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the UniFi adapter through
 * SyncHostFromAdapter. Mocks the UniFi Network Integration API,
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
            SyncHostFromAdapter::run($record);
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

    private function configuredUnifiAdapter(string $siteId = 'default'): UnifiAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'unifi')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://unifi.example.test:8443');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-unifi-api-key'));
        SyncAdapterConfig::put($instance->id, 'site_id', $siteId);

        return new UnifiAdapter($instance->fresh());
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
