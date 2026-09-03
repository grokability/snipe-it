<?php

namespace Tests\Feature\SyncAdapters\Intune;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Intune\IntuneAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Microsoft Intune adapter through
 * SyncHostFromAdapter. Fakes the OAuth token exchange and the
 * Graph managedDevices endpoint.
 */
class IntuneAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_devices_from_graph_and_creates_assets()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [
                    $this->intuneDevice(id: 'guid-42', name: 'intune-host-1'),
                    $this->intuneDevice(id: 'guid-99', name: 'intune-host-2'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'intune', 'external_id' => 'guid-42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'intune', 'external_id' => 'guid-99']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [
                    [
                        'id' => 'guid-7',
                        'deviceName' => 'intune-mbp',
                        'serialNumber' => 'FW-ABC-123',
                        'model' => 'Surface Pro 9',
                        'manufacturer' => 'Microsoft',
                        'ethernetMacAddress' => 'aa:bb:cc:dd:ee:ff',
                        'wiFiMacAddress' => '11:22:33:44:55:66',
                        'operatingSystem' => 'Windows',
                        'osVersion' => '11.24H2',
                        'lastSyncDateTime' => '2026-01-15T10:00:00Z',
                        'userPrincipalName' => 'alice@example.test',
                        'complianceState' => 'compliant',
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('intune', $record->sourceKey);
        $this->assertSame('guid-7', $record->sourceId);
        $this->assertSame('intune-mbp', $record->hostname);
        $this->assertSame('FW-ABC-123', $record->hardwareSerial);
        $this->assertSame('Surface Pro 9', $record->hardwareModel);
        $this->assertSame('Microsoft', $record->manufacturer);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('Windows', $record->os);
        $this->assertSame('11.24H2', $record->osVersion);
        $this->assertSame('alice@example.test', $record->assignedUserEmail);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredAdapter(): IntuneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://graph.microsoft.com');
        SyncAdapterConfig::put($instance->id, 'tenant_id', 'stub-tenant');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-secret'));

        return new IntuneAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function intuneDevice(string $id, string $name): array
    {
        return [
            'id' => $id,
            'deviceName' => $name,
            'serialNumber' => 'SN-'.$id,
            'model' => 'Generic Model',
        ];
    }
}
