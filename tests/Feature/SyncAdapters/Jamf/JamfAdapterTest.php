<?php

namespace Tests\Feature\SyncAdapters\Jamf;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Jamf Pro adapter through
 * SyncHostFromAdapter. Mocks the Jamf Pro API, asserts assets +
 * asset_external_sources land correctly, and that the totalCount /
 * pagination termination works.
 */
class JamfAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_computers_from_jamf_and_creates_assets()
    {
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 2,
                'results' => [
                    $this->jamfComputer(id: 42, name: 'lab-mac-01', model: 'iMac'),
                    $this->jamfComputer(id: 99, name: 'lab-mac-02', model: 'Mac mini'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => '42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'jamf', 'external_id' => '99']);
        $this->assertDatabaseHas('assets', ['name' => 'lab-mac-01']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredJamfAdapter();

        Http::fake([
            '*/api/v1/computers-inventory*' => Http::response([
                'totalCount' => 1,
                'results' => [
                    $this->jamfComputer(
                        id: 7,
                        name: 'design-mbp',
                        model: 'MacBook Pro (16-inch, M3)',
                        serialNumber: 'C02XYZ',
                        macAddress: 'aa:bb:cc:11:22:33',
                        osName: 'macOS',
                        osVersion: '14.2.1',
                        lastContactTime: '2026-01-15T10:00:00.000Z',
                    ),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('jamf', $record->sourceKey);
        $this->assertSame('7', $record->sourceId);
        $this->assertSame('design-mbp', $record->hostname);
        $this->assertSame('MacBook Pro (16-inch, M3)', $record->hardwareModel);
        $this->assertSame('C02XYZ', $record->hardwareSerial);
        $this->assertSame('Apple', $record->manufacturer);
        $this->assertSame('aa:bb:cc:11:22:33', $record->primaryMac);
        $this->assertSame('macOS', $record->os);
        $this->assertSame('14.2.1', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredJamfAdapter(): JamfAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.jamfcloud.com');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-jamf-token'));

        return new JamfAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function jamfComputer(
        int $id,
        string $name = 'computer',
        string $model = 'MacBook',
        ?string $serialNumber = null,
        ?string $macAddress = null,
        ?string $osName = 'macOS',
        ?string $osVersion = null,
        ?string $lastContactTime = null,
    ): array {
        return [
            'id' => (string) $id,
            'udid' => 'jamf-udid-'.$id,
            'general' => [
                'name' => $name,
                'lastContactTime' => $lastContactTime,
                'lastEnrolledDate' => null,
            ],
            'hardware' => [
                'make' => 'Apple',
                'model' => $model,
                'modelIdentifier' => 'Mac'.$id,
                'serialNumber' => $serialNumber,
                'macAddress' => $macAddress,
            ],
            'operatingSystem' => [
                'name' => $osName,
                'version' => $osVersion,
            ],
        ];
    }
}
