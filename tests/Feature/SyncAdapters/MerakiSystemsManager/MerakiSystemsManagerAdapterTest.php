<?php

namespace Tests\Feature\SyncAdapters\MerakiSystemsManager;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\MerakiSystemsManager\MerakiClient;
use App\SyncAdapters\MerakiSystemsManager\MerakiSystemsManagerAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Meraki Systems Manager adapter through
 * SyncAdapter. Fakes the org->networks list and per-network
 * SM devices call.
 */
class MerakiSystemsManagerAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_meraki_and_creates_assets()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/organizations/*/networks*' => Http::response([
                ['id' => 'N_100', 'productTypes' => ['systemsManager']],
                ['id' => 'N_200', 'productTypes' => ['wireless']], // filtered out
            ]),
            '*/networks/N_100/sm/devices*' => Http::response([
                $this->merakiDevice(id: 'D_42', name: 'meraki-host-1'),
                $this->merakiDevice(id: 'D_99', name: 'meraki-host-2'),
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'meraki_sm', 'external_id' => 'D_42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'meraki_sm', 'external_id' => 'D_99']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/organizations/*/networks*' => Http::response([
                ['id' => 'N_100', 'productTypes' => ['systemsManager']],
            ]),
            '*/networks/N_100/sm/devices*' => Http::response([
                [
                    'id' => 'D_7',
                    'name' => 'meraki-mbp',
                    'serialNumber' => 'ABC-123',
                    'systemModel' => 'MacBook Pro',
                    'manufacturer' => 'Apple',
                    'wifiMac' => 'aa:bb:cc:dd:ee:ff',
                    'ip' => '10.0.0.7',
                    'osName' => 'macOS',
                    'systemType' => '14.5',
                    'lastConnectAt' => '2026-01-15T10:00:00Z',
                    'ownerUsername' => 'alice',
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('meraki_sm', $record->sourceKey);
        $this->assertSame('D_7', $record->sourceId);
        $this->assertSame('meraki-mbp', $record->hostname);
        $this->assertSame('ABC-123', $record->hardwareSerial);
        $this->assertSame('MacBook Pro', $record->hardwareModel);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('10.0.0.7', $record->primaryIp);
        $this->assertSame('macOS', $record->os);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_follows_next_link_and_stops_on_short_page()
    {
        $nextUrl = 'https://api.meraki.com/api/v1/networks/N_100/sm/devices?perPage=2&startingAfter=D_2';

        Http::fake([
            '*/organizations/*/networks*' => Http::response([
                ['id' => 'N_100', 'productTypes' => ['systemsManager']],
            ]),
            '*/networks/N_100/sm/devices*' => Http::sequence()
                ->push([
                    $this->merakiDevice(id: 'D_1', name: 'meraki-host-1'),
                    $this->merakiDevice(id: 'D_2', name: 'meraki-host-2'),
                ], 200, ['Link' => '<'.$nextUrl.'>; rel=next'])
                ->push([
                    $this->merakiDevice(id: 'D_3', name: 'meraki-host-3'),
                ], 200, ['Link' => '<https://api.meraki.com/api/v1/networks/N_100/sm/devices?perPage=2&startingAfter=D_3>; rel=next']),
        ]);

        $client = new MerakiClient('https://api.meraki.com/api/v1', 'fake-key', '111111');
        $devices = iterator_to_array($client->devices(perPage: 2), false);

        $this->assertSame(['D_1', 'D_2', 'D_3'], array_column($devices, 'id'));
        Http::assertSent(fn (Request $request) => $request->url() === $nextUrl);
        Http::assertSentCount(3);
    }

    private function configuredAdapter(): MerakiSystemsManagerAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'meraki_sm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://api.meraki.com/api/v1');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-key'));
        SyncAdapterConfig::put($instance->id, 'organization_id', '111111');

        return new MerakiSystemsManagerAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function merakiDevice(string $id, string $name): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'serialNumber' => 'SN-'.$id,
            'systemModel' => 'Generic Model',
        ];
    }
}
