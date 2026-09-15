<?php

namespace Tests\Feature\SyncAdapters\Zentral;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\SyncHostFromAdapter;
use App\SyncAdapters\Zentral\ZentralAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Zentral adapter through SyncHostFromAdapter.
 * Fakes Zentral's /api/inventory/machines/ endpoint, asserts assets +
 * asset_external_sources rows land correctly.
 */
class ZentralAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_machines_from_zentral_and_creates_assets()
    {
        $adapter = $this->configuredZentralAdapter();

        Http::fake([
            '*/api/inventory/machines/*' => Http::sequence()
                ->push($this->zentralResponse([
                    $this->zentralMachine(serial: 'ABC123', hostname: 'zentral-host-1'),
                    $this->zentralMachine(serial: 'XYZ789', hostname: 'zentral-host-2'),
                ])),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'zentral', 'external_id' => 'ABC123']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'zentral', 'external_id' => 'XYZ789']);
        $this->assertDatabaseHas('assets', ['name' => 'zentral-host-1']);
        $this->assertDatabaseHas('assets', ['name' => 'zentral-host-2']);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredZentralAdapter();

        Http::fake([
            '*/api/inventory/machines/*' => Http::sequence()
                ->push($this->zentralResponse([
                    [
                        'serial_number' => 'FW-ABC-123',
                        'computer_name' => 'framework-13',
                        'system_info' => [
                            'hardware_model' => 'Framework 13',
                            'hardware_vendor' => 'Framework',
                        ],
                        'network_interfaces' => [
                            ['mac' => 'aa:bb:cc:dd:ee:ff', 'address' => '10.0.0.7'],
                        ],
                        'os_version' => ['name' => 'macOS', 'version' => '14.5'],
                        'last_seen' => '2026-01-15T10:00:00Z',
                    ],
                ])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('zentral', $record->sourceKey);
        $this->assertSame('FW-ABC-123', $record->sourceId);
        $this->assertSame('framework-13', $record->hostname);
        $this->assertSame('Framework 13', $record->hardwareModel);
        $this->assertSame('Framework', $record->manufacturer);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('10.0.0.7', $record->primaryIp);
        $this->assertSame('macOS', $record->os);
        $this->assertSame('14.5', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    private function configuredZentralAdapter(): ZentralAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'zentral')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://zentral.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return new ZentralAdapter($instance->fresh());
    }

    /**
     * @param  array<int, array<string, mixed>>  $machines
     * @return array<string, mixed>
     */
    private function zentralResponse(array $machines): array
    {
        return ['count' => count($machines), 'results' => $machines];
    }

    /**
     * @return array<string, mixed>
     */
    private function zentralMachine(string $serial, string $hostname): array
    {
        return [
            'serial_number' => $serial,
            'computer_name' => $hostname,
            'system_info' => ['hardware_model' => 'Generic Model'],
        ];
    }
}
