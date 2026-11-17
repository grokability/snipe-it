<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Services\AppleDb\AppleDbEnrichment;
use App\SyncAdapters\Fleet\FleetAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Cross-adapter identity dedup. ABM reports Apple's long marketing
 * name (e.g. "MacBook Pro (14-inch, M4, 2024)"), Fleet reports the
 * shorter osquery-emitted name ("MacBook Pro") for the same physical
 * hardware. Without the shared canonical identity, each adapter
 * would create its own AssetModel row and admins would be left with
 * duplicates to merge manually.
 *
 * The resolver uses the record's canonicalMarketingName + canonical
 * ModelNumber (populated by AppleDB enrichment during normalize())
 * to pick up the existing row even when the adapter's own
 * $modelName string differs from what's stored.
 */
class CrossAdapterIdentityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
        Storage::fake('public');
        AppleDbEnrichment::clearCache();
    }

    public function test_fleet_sync_reuses_an_existing_model_created_under_abms_longer_marketing_name()
    {
        // Pre-state: ABM already synced this hardware, leaving an
        // AssetModel with the long marketing name as its name,
        // mfg_marketing_name stamped with the AppleDB canonical, and
        // model_number stamped with Apple's regulatory number. The
        // canonical values are what Fleet's enrichment will converge
        // on when it looks the same productType up.
        $existing = AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'mfg_marketing_name' => 'MacBook Pro (14-inch, M4, 2024)',
            'model_number' => 'A3123',
        ]);

        $adapter = $this->configuredFleetAdapter(enrichmentEnabled: true);

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response(['hosts' => [
                [
                    'id' => 1,
                    'hostname' => 'mac-01',
                    'hardware_model' => 'MacBookPro18,3',
                    'hardware_marketing_name' => 'MacBook Pro',
                    'hardware_vendor' => 'Apple Inc.',
                    'hardware_serial' => 'C02MAC01',
                    'platform' => 'darwin',
                    'uuid' => 'fleet-mac-01',
                ],
            ]]),
            'api.appledb.dev/device/*' => Http::response([
                'name' => 'MacBook Pro (14-inch, M4, 2024)',
                'released' => '2024-11-08',
                'discontinued' => '2026-03-05',
                'model' => ['A3123'],
                'imageKey' => 'macbook-pro-14-m4',
                'colors' => [['key' => 'silver']],
            ]),
            'img.appledb.dev/*' => Http::response(str_repeat("\x89PNG\r\n\x1a\n", 4)),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertSame(1, AssetModel::query()->count());
        $assetModel = AssetModel::query()->first();
        $this->assertSame($existing->id, $assetModel->id);
        // Fleet did not rename the row to its own shorter marketing name.
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $assetModel->name);
    }

    public function test_first_fleet_sync_stamps_canonical_values_on_new_model_row()
    {
        $adapter = $this->configuredFleetAdapter(enrichmentEnabled: true);

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response(['hosts' => [
                [
                    'id' => 1,
                    'hostname' => 'mac-01',
                    'hardware_model' => 'MacBookPro18,3',
                    'hardware_marketing_name' => 'MacBook Pro',
                    'hardware_vendor' => 'Apple Inc.',
                    'hardware_serial' => 'C02MAC01',
                    'platform' => 'darwin',
                    'uuid' => 'fleet-mac-01',
                ],
            ]]),
            'api.appledb.dev/device/*' => Http::response([
                'name' => 'MacBook Pro (14-inch, M4, 2024)',
                'released' => '2024-11-08',
                'discontinued' => '2026-03-05',
                'model' => ['A3123'],
                'imageKey' => 'macbook-pro-14-m4',
                'colors' => [['key' => 'silver']],
            ]),
            'img.appledb.dev/*' => Http::response(str_repeat("\x89PNG\r\n\x1a\n", 4)),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertSame(1, AssetModel::query()->count());
        $model = AssetModel::query()->first();
        // Fleet's own marketing name is used as the AssetModel name per the adapter's mapping. Canonical values are stamped alongside so a later ABM sync finds this row via the OR match.
        $this->assertSame('MacBook Pro', $model->name);
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $model->mfg_marketing_name);
        $this->assertSame('A3123', $model->model_number);
    }

    public function test_resolver_falls_back_to_name_match_when_canonical_values_are_absent()
    {
        // Non-Apple hosts never carry canonical values (adapter's isAppleHost gate skips the lookup). Framework must still find existing models by exact name match as it has always done.
        $existing = AssetModel::factory()->create([
            'name' => 'ThinkPad X1 Carbon',
        ]);

        $adapter = $this->configuredFleetAdapter(enrichmentEnabled: true);

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response(['hosts' => [
                [
                    'id' => 2,
                    'hostname' => 'linux-01',
                    'hardware_model' => 'ThinkPad X1 Carbon',
                    'hardware_vendor' => 'Lenovo',
                    'hardware_serial' => 'LNV1',
                    'platform' => 'linux',
                    'uuid' => 'fleet-linux-01',
                ],
            ]]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertSame(1, AssetModel::query()->count());
        $this->assertSame($existing->id, AssetModel::query()->first()->id);
    }

    private function configuredFleetAdapter(bool $enrichmentEnabled): FleetAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://fleet.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));
        SyncAdapterConfig::put($instance->id, 'use_appledb_enrichment', $enrichmentEnabled ? '1' : '');
        SyncAdapterConfig::put($instance->id, 'mapping.fleet_model_marketing_name', 'native:model');

        return new FleetAdapter($instance->fresh());
    }
}
