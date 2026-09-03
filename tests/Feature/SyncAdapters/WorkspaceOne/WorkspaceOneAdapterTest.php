<?php

namespace Tests\Feature\SyncAdapters\WorkspaceOne;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\SyncHostFromAdapter;
use App\SyncAdapters\WorkspaceOne\WorkspaceOneAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Omnissa Workspace ONE adapter through
 * SyncHostFromAdapter. Fakes the OAuth token exchange and the
 * /api/mdm/devices/search endpoint.
 */
class WorkspaceOneAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_workspace_one_and_creates_assets()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/connect/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/api/mdm/devices/search*' => Http::response([
                'Total' => 2,
                'PageSize' => 500,
                'Page' => 0,
                'Devices' => [
                    $this->ws1Device(uuid: 'ws1-42', friendlyName: 'ws1-host-1'),
                    $this->ws1Device(uuid: 'ws1-99', friendlyName: 'ws1-host-2'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'workspace_one', 'external_id' => 'ws1-42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'workspace_one', 'external_id' => 'ws1-99']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/connect/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/api/mdm/devices/search*' => Http::response([
                'Total' => 1,
                'PageSize' => 500,
                'Page' => 0,
                'Devices' => [
                    [
                        'Uuid' => 'ws1-7',
                        'DeviceFriendlyName' => 'ws1-ipad',
                        'SerialNumber' => 'ABC-123',
                        'Model' => 'iPad Pro',
                        'DeviceManufacturer' => 'Apple',
                        'MacAddress' => 'aa:bb:cc:dd:ee:ff',
                        'IpAddress' => '10.0.0.7',
                        'Platform' => 'AppleOsX',
                        'OperatingSystem' => '17.4',
                        'LastSeen' => '2026-01-15T10:00:00Z',
                        'UserEmailAddress' => 'alice@example.test',
                        'UserName' => 'alice',
                        'ComplianceStatus' => 'Compliant',
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('workspace_one', $record->sourceKey);
        $this->assertSame('ws1-7', $record->sourceId);
        $this->assertSame('ws1-ipad', $record->hostname);
        $this->assertSame('ABC-123', $record->hardwareSerial);
        $this->assertSame('iPad Pro', $record->hardwareModel);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('10.0.0.7', $record->primaryIp);
        $this->assertSame('AppleOsX', $record->os);
        $this->assertSame('alice@example.test', $record->assignedUserEmail);
        $this->assertSame('alice', $record->assignedUserName);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredAdapter(): WorkspaceOneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'workspace_one')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://as123.awmdm.com');
        SyncAdapterConfig::put($instance->id, 'tenant_code', 'stub-tenant');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-secret'));

        return new WorkspaceOneAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function ws1Device(string $uuid, string $friendlyName): array
    {
        return [
            'Uuid' => $uuid,
            'DeviceFriendlyName' => $friendlyName,
            'SerialNumber' => 'SN-'.$uuid,
            'Model' => 'Generic Model',
        ];
    }
}
