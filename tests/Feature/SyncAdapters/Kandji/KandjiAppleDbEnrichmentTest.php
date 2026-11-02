<?php

namespace Tests\Feature\SyncAdapters\Kandji;

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Services\AppleDb\AppleDbEnrichment;
use App\SyncAdapters\Kandji\KandjiAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KandjiAppleDbEnrichmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
        Storage::fake('public');
        AppleDbEnrichment::clearCache();
    }

    public function test_pull_backfills_model_fields_from_appledb_via_details_endpoint()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        $model = AssetModel::factory()->create([
            'name' => 'MacBook Pro',
            'image' => null,
            'model_number' => null,
            'mfg_marketing_name' => null,
            'mfg_release_date' => null,
            'mfg_discontinued_date' => null,
        ]);

        $this->fakeKandjiAndAppleDb();

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model->refresh();
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $model->mfg_marketing_name);
        $this->assertSame('A3123', $model->model_number);
        $this->assertSame('2024-11-08', $model->mfg_release_date?->toDateString());
        $this->assertSame('2026-03-05', $model->mfg_discontinued_date?->toDateString());
    }

    public function test_pull_skips_detail_fetches_entirely_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);
        $this->fakeKandjiAndAppleDb();

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/details'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_record_extras_carry_appledb_values_and_kandji_identifier()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        $this->fakeKandjiAndAppleDb();

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('MacBookPro18,3', $records[0]->extra['kandji_model_identifier']);
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $records[0]->extra['appledb_marketing_name']);
        $this->assertSame('A3123', $records[0]->extra['appledb_model_number']);
    }

    public function test_detail_fetch_failure_leaves_identifier_null_without_aborting_the_pull()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);

        Http::fake([
            '*/api/v1/devices*/details' => Http::response([], 500),
            '*/api/v1/devices*' => Http::response([[
                'device_id' => 'kandji-1',
                'device_name' => 'mac-01',
                'model' => 'MacBook Pro',
                'serial_number' => 'KJ-1',
                'platform' => 'Mac',
                'os_version' => '15.0',
                'last_check_in' => null,
                'asset_tag' => null,
                'assigned_blueprint_id' => null,
                'mdm_enabled' => true,
            ]]),
            'api.appledb.dev/*' => Http::response([]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        $this->assertNull($records[0]->extra['kandji_model_identifier']);
    }

    private function fakeKandjiAndAppleDb(): void
    {
        Http::fake([
            '*/api/v1/devices/kandji-1/details' => Http::response([
                'hardware_overview' => [
                    'model' => 'MacBook Pro (14-inch, M4, 2024)',
                    'model_identifier' => 'MacBookPro18,3',
                ],
            ]),
            '*/api/v1/devices*' => Http::response([[
                'device_id' => 'kandji-1',
                'device_name' => 'mac-01',
                'model' => 'MacBook Pro',
                'serial_number' => 'KJ-1',
                'platform' => 'Mac',
                'os_version' => '15.0',
                'last_check_in' => null,
                'asset_tag' => null,
                'assigned_blueprint_id' => null,
                'mdm_enabled' => true,
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
    }

    private function configuredAdapter(bool $enrichmentEnabled): KandjiAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'kandji')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.api.kandji.io');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-kandji-token'));
        SyncAdapterConfig::put($instance->id, 'use_appledb_enrichment', $enrichmentEnabled ? '1' : '');

        return new KandjiAdapter($instance->fresh());
    }
}
