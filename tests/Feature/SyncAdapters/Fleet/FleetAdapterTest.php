<?php

namespace Tests\Feature\SyncAdapters\Fleet;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Fleet\FleetAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Fleet adapter through SyncHostFromAdapter.
 * Mocks Fleet's REST API, asserts assets + asset_external_sources + side-
 * table rows land correctly, and that a re-run updates instead of
 * duplicating.
 *
 * The adapter is instance-bound, so every test creates a
 * SyncAdapterInstance first and populates its URL + token via
 * SyncAdapterConfig.
 */
class FleetAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SyncHostFromAdapter picks a deployable status label or falls
        // back to any status label. Seed at least one so create doesn't
        // blow up.
        Statuslabel::factory()->rtd()->create();
    }

    public function test_pulls_hosts_from_fleet_and_creates_assets()
    {
        $adapter = $this->configuredFleetAdapter();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push($this->fleetHostsResponse([
                    // primary_mac included so the external-inventory
                    // side-table row is actually written. the mapping
                    // path skips null values by design to keep partial
                    // syncs from blanking existing data.
                    $this->fleetHost(id: 42, hostname: 'workstation-01', hardware_model: 'MacBook Pro 16-inch (M3)', primary_mac: 'aa:bb:cc:00:00:42'),
                    $this->fleetHost(id: 99, hostname: 'workstation-02', hardware_model: 'ThinkPad X1 Carbon', primary_mac: 'aa:bb:cc:00:00:99'),
                ])),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'fleet', 'external_id' => '42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'fleet', 'external_id' => '99']);

        $this->assertDatabaseCount('asset_external_sources', 2);

        $this->assertDatabaseHas('assets', ['name' => 'workstation-01']);
        $this->assertDatabaseHas('assets', ['name' => 'workstation-02']);
    }

    public function test_re_running_updates_existing_assets_instead_of_duplicating()
    {
        $adapter = $this->configuredFleetAdapter();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                // first pull: one host
                ->push($this->fleetHostsResponse([
                    $this->fleetHost(id: 42, hostname: 'workstation-01', hardware_model: 'MacBook Pro'),
                ]))
                // second pull: same host with a new hostname + IP
                ->push($this->fleetHostsResponse([
                    $this->fleetHost(id: 42, hostname: 'workstation-01-renamed', hardware_model: 'MacBook Pro', primary_ip: '10.0.0.42'),
                ])),
        ]);

        // First pull.
        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 1);
        $this->assertDatabaseCount('assets', 1);
        $originalAssetId = Asset::where('name', 'workstation-01')->firstOrFail()->id;

        // Second pull.
        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        // No new rows: same host, same identity link.
        $this->assertDatabaseCount('asset_external_sources', 1);
        $this->assertDatabaseCount('assets', 1);

        $updated = Asset::find($originalAssetId);
        $this->assertSame('workstation-01-renamed', $updated->name);

        $inventory = DB::table('asset_external_sources')->where('asset_id', $originalAssetId)->first();
        $this->assertSame('10.0.0.42', $inventory->primary_ip);
    }

    public function test_normalized_record_carries_expected_fields()
    {
        $adapter = $this->configuredFleetAdapter();

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push($this->fleetHostsResponse([
                    $this->fleetHost(
                        id: 7,
                        hostname: 'zed',
                        hardware_model: 'Framework 13',
                        hardware_serial: 'FW-ABC-123',
                        hardware_vendor: 'Framework',
                        primary_mac: 'aa:bb:cc:dd:ee:ff',
                        primary_ip: '192.168.1.7',
                        platform: 'linux',
                        os_version: 'Ubuntu 24.04',
                        seen_time: '2026-01-15T10:00:00Z',
                    ),
                ])),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('fleet', $record->sourceKey);
        $this->assertSame('7', $record->sourceId);
        $this->assertSame('zed', $record->hostname);
        $this->assertSame('Framework 13', $record->hardwareModel);
        $this->assertSame('FW-ABC-123', $record->hardwareSerial);
        $this->assertSame('Framework', $record->manufacturer);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('192.168.1.7', $record->primaryIp);
        $this->assertSame('linux', $record->os);
        $this->assertSame('Ubuntu 24.04', $record->osVersion);
        $this->assertNotNull($record->lastSeen);
    }

    public function test_synced_assets_inherit_the_instance_company_id()
    {
        $company = \App\Models\Company::factory()->create();
        $instance = SyncAdapterInstance::create([
            'adapter_type' => 'fleet',
            'label' => 'Company Fleet',
            'company_id' => $company->id,
        ]);
        SyncAdapterConfig::put($instance->id, 'url', 'https://fleet.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push($this->fleetHostsResponse([
                    $this->fleetHost(id: 1, hostname: 'scoped-host', hardware_model: 'MacBook'),
                ])),
        ]);

        $adapter = new FleetAdapter($instance);
        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseHas('assets', ['name' => 'scoped-host', 'company_id' => $company->id]);
        $this->assertDatabaseHas('asset_external_sources', ['source' => $instance->slug, 'company_id' => $company->id]);
    }

    /** Builds and hydrates the built-in Fleet instance seeded by the migration. */
    private function configuredFleetAdapter(): FleetAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://fleet.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return new FleetAdapter($instance->fresh());
    }

    /**
     * @param  array<int, array<string, mixed>>  $hosts
     * @return array<string, mixed>
     */
    private function fleetHostsResponse(array $hosts): array
    {
        return ['hosts' => $hosts];
    }

    /**
     * @return array<string, mixed>
     */
    private function fleetHost(
        int $id,
        string $hostname = 'host',
        string $hardware_model = 'Generic Laptop',
        ?string $hardware_serial = null,
        ?string $hardware_vendor = null,
        ?string $primary_mac = null,
        ?string $primary_ip = null,
        ?string $platform = null,
        ?string $os_version = null,
        ?string $seen_time = null,
    ): array {
        return [
            'id' => $id,
            'hostname' => $hostname,
            'hardware_model' => $hardware_model,
            'hardware_serial' => $hardware_serial,
            'hardware_vendor' => $hardware_vendor,
            'primary_mac' => $primary_mac,
            'primary_ip' => $primary_ip,
            'platform' => $platform,
            'os_version' => $os_version,
            'seen_time' => $seen_time,
            'uuid' => 'fleet-uuid-'.$id,
        ];
    }
}
