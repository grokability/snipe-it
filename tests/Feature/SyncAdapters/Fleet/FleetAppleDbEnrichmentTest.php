<?php

namespace Tests\Feature\SyncAdapters\Fleet;

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

class FleetAppleDbEnrichmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
        Storage::fake('public');
        AppleDbEnrichment::clearCache();
    }

    public function test_pull_backfills_apple_model_fields_from_appledb()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        $model = AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => null,
            'model_number' => null,
            'mfg_marketing_name' => null,
            'mfg_release_date' => null,
            'mfg_discontinued_date' => null,
        ]);

        $this->fakeFleetAndAppleDb([$this->appleHost(id: 1)]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model->refresh();
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $model->mfg_marketing_name);
        $this->assertSame('A3123', $model->model_number);
        $this->assertSame('2024-11-08', $model->mfg_release_date?->toDateString());
        $this->assertSame('2026-03-05', $model->mfg_discontinued_date?->toDateString());
        $this->assertNotNull($model->image);
        Storage::disk('public')->assertExists('models/'.$model->image);
    }

    public function test_pull_skips_appledb_calls_entirely_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);

        $this->fakeFleetAndAppleDb([$this->appleHost(id: 1)]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_pull_skips_appledb_for_non_apple_hosts_even_when_toggle_is_on()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);

        $this->fakeFleetAndAppleDb([
            $this->linuxHost(id: 1),
            $this->windowsHost(id: 2),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_record_extras_carry_appledb_values_for_custom_field_mapping()
    {
        // AppleDB values flow through HostInventoryRecord::$extra so admins can map any of them to a custom field on the asset (e.g. "Original Release Date" column admins can sort by in asset reports).
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);

        $this->fakeFleetAndAppleDb([$this->appleHost(id: 1)]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $records[0]->extra['appledb_marketing_name']);
        $this->assertSame('A3123', $records[0]->extra['appledb_model_number']);
        $this->assertSame('2024-11-08', $records[0]->extra['appledb_release_date']);
        $this->assertSame('2026-03-05', $records[0]->extra['appledb_discontinued_date']);
    }

    public function test_record_extras_appledb_values_null_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);

        $this->fakeFleetAndAppleDb([$this->appleHost(id: 1)]);

        $records = iterator_to_array($adapter->pull());

        $this->assertNull($records[0]->extra['appledb_marketing_name']);
        $this->assertNull($records[0]->extra['appledb_model_number']);
        $this->assertNull($records[0]->extra['appledb_release_date']);
        $this->assertNull($records[0]->extra['appledb_discontinued_date']);
    }

    public function test_extra_fields_declaration_hides_appledb_keys_when_toggle_is_off()
    {
        $enabled = $this->configuredAdapter(enrichmentEnabled: true);
        $this->assertArrayHasKey('appledb_marketing_name', $enabled->extraFields());
        $this->assertArrayHasKey('appledb_release_date', $enabled->extraFields());
        $this->assertArrayHasKey('appledb_discontinued_date', $enabled->extraFields());

        $disabled = $this->configuredAdapter(enrichmentEnabled: false);
        $this->assertArrayNotHasKey('appledb_marketing_name', $disabled->extraFields());
        $this->assertArrayNotHasKey('appledb_release_date', $disabled->extraFields());
        $this->assertArrayNotHasKey('appledb_discontinued_date', $disabled->extraFields());
    }

    public function test_record_extras_appledb_values_null_for_non_apple_hosts()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);

        $this->fakeFleetAndAppleDb([$this->linuxHost(id: 1)]);

        $records = iterator_to_array($adapter->pull());

        $this->assertNull($records[0]->extra['appledb_marketing_name']);
        $this->assertNull($records[0]->extra['appledb_model_number']);
        $this->assertNull($records[0]->extra['appledb_release_date']);
        $this->assertNull($records[0]->extra['appledb_discontinued_date']);
    }

    public function test_pull_only_hits_appledb_once_per_apple_hardware_model()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => null,
        ]);

        $this->fakeFleetAndAppleDb(array_map(
            fn (int $i) => $this->appleHost(id: $i),
            range(1, 5),
        ));

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $infoCount = 0;
        Http::assertSent(function ($request) use (&$infoCount) {
            if (str_contains($request->url(), 'api.appledb.dev')) {
                $infoCount++;
            }

            return true;
        });
        $this->assertSame(1, $infoCount);
    }

    /**
     * @param  array<int, array<string, mixed>>  $hosts
     */
    private function fakeFleetAndAppleDb(array $hosts): void
    {
        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::response(['hosts' => $hosts]),
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
    }

    /**
     * @return array<string, mixed>
     */
    private function appleHost(int $id): array
    {
        return [
            'id' => $id,
            'hostname' => 'mac-'.$id,
            'hardware_model' => 'MacBookPro18,3',
            'hardware_marketing_name' => 'MacBook Pro (14-inch, M4, 2024)',
            'hardware_vendor' => 'Apple Inc.',
            'hardware_serial' => 'C02MAC'.$id,
            'platform' => 'darwin',
            'uuid' => 'fleet-mac-'.$id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function linuxHost(int $id): array
    {
        return [
            'id' => $id,
            'hostname' => 'linux-'.$id,
            'hardware_model' => 'ThinkPad X1 Carbon',
            'hardware_vendor' => 'Lenovo',
            'hardware_serial' => 'LN'.$id,
            'platform' => 'linux',
            'uuid' => 'fleet-linux-'.$id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function windowsHost(int $id): array
    {
        return [
            'id' => $id,
            'hostname' => 'win-'.$id,
            'hardware_model' => 'Surface Laptop 5',
            'hardware_vendor' => 'Microsoft Corporation',
            'hardware_serial' => 'MS'.$id,
            'platform' => 'windows',
            'uuid' => 'fleet-win-'.$id,
        ];
    }

    private function configuredAdapter(bool $enrichmentEnabled): FleetAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://fleet.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));
        SyncAdapterConfig::put($instance->id, 'use_appledb_enrichment', $enrichmentEnabled ? '1' : '');
        // Route the Fleet marketing name to native:model so the AssetModel's name mirrors how the enrichment flow resolves it.
        SyncAdapterConfig::put($instance->id, 'mapping.fleet_model_marketing_name', 'native:model');

        return new FleetAdapter($instance->fresh());
    }
}
