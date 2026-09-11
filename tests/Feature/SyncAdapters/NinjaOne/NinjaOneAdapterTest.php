<?php

namespace Tests\Feature\SyncAdapters\NinjaOne;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\NinjaOne\NinjaOneAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the NinjaOne adapter through SyncHostFromAdapter.
 * Fakes the OAuth token exchange and the /v2/devices-detailed endpoint.
 */
class NinjaOneAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_ninjaone_and_creates_assets()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/ws/oauth/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v2/devices-detailed*' => Http::response([
                $this->ninjaDevice(id: 42, name: 'ninja-host-1'),
                $this->ninjaDevice(id: 99, name: 'ninja-host-2'),
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'ninjaone', 'external_id' => '42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'ninjaone', 'external_id' => '99']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/ws/oauth/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v2/devices-detailed*' => Http::response([
                [
                    'id' => 7,
                    'systemName' => 'ninja-mbp',
                    'dnsName' => 'ninja-mbp.local',
                    'system' => [
                        'serialNumber' => 'FW-ABC-123',
                        'model' => 'Framework 13',
                        'manufacturer' => 'Framework',
                    ],
                    'os' => [
                        'name' => 'Ubuntu',
                        'buildNumber' => '24.04.1',
                    ],
                    'lastContact' => 1_768_953_600,
                    'publicIP' => '203.0.113.7',
                    'organizationId' => 42,
                    'locationId' => 1,
                    'userTitle' => 'alice',
                    'nodeClass' => 'LINUX_WORKSTATION',
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('ninjaone', $record->sourceKey);
        $this->assertSame('7', $record->sourceId);
        $this->assertSame('ninja-mbp', $record->hostname);
        $this->assertSame('FW-ABC-123', $record->hardwareSerial);
        $this->assertSame('Framework 13', $record->hardwareModel);
        $this->assertSame('Framework', $record->manufacturer);
        $this->assertSame('203.0.113.7', $record->primaryIp);
        $this->assertSame('Ubuntu', $record->os);
        $this->assertSame('24.04.1', $record->osVersion);
        $this->assertSame('alice', $record->assignedUserName);
        $this->assertSame('42', $record->vendorGroupId);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_zero_last_contact_does_not_produce_epoch_1970()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/ws/oauth/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v2/devices-detailed*' => Http::response([
                $this->ninjaDevice(id: 1, name: 'never-checked-in') + ['lastContact' => 0],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertNull($records[0]->lastSeen);
    }

    private function configuredAdapter(): NinjaOneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'ninjaone')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://app.ninjarmm.com');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-secret'));

        return new NinjaOneAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function ninjaDevice(int $id, string $name): array
    {
        return [
            'id' => $id,
            'systemName' => $name,
            'system' => [
                'serialNumber' => 'SN-'.$id,
                'model' => 'Generic Model',
            ],
        ];
    }
}
