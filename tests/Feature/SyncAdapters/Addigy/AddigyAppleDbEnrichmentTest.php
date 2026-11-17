<?php

namespace Tests\Feature\SyncAdapters\Addigy;

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Services\AppleDb\AppleDbEnrichment;
use App\SyncAdapters\Addigy\AddigyAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Coverage for Addigy's AppleDB enrichment path. Addigy's v2 API is
 * fact-request shaped: the client lists the fact identifiers it
 * wants on every POST /devices call, and the response is a facts
 * map. The AppleDB-shape identifier lives in the `product_name`
 * fact per Addigy's device-facts catalog.
 */
class AddigyAppleDbEnrichmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
        Storage::fake('public');
        AppleDbEnrichment::clearCache();
    }

    public function test_pull_backfills_model_fields_from_appledb_using_product_name_fact()
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

        $this->fakeAddigyAndAppleDb();

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model->refresh();
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $model->mfg_marketing_name);
        $this->assertSame('A3123', $model->model_number);
        $this->assertSame('2024-11-08', $model->mfg_release_date?->toDateString());
    }

    public function test_pull_skips_appledb_entirely_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);
        $this->fakeAddigyAndAppleDb();

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_new_mappable_fact_values_land_on_record_extras()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);
        $this->fakeAddigyAndAppleDb();

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('24A5309e', $records[0]->extra['addigy_build_version']);
        $this->assertSame('UDID-ADDIGY-1', $records[0]->extra['addigy_udid']);
        $this->assertTrue($records[0]->extra['addigy_has_mdm']);
        $this->assertTrue($records[0]->extra['addigy_filevault_enabled']);
        $this->assertFalse($records[0]->extra['addigy_firewall_enabled']);
        $this->assertSame('16', $records[0]->extra['addigy_total_memory_gb']);
        $this->assertSame('500', $records[0]->extra['addigy_disk_total_space_gb']);
        $this->assertSame('250', $records[0]->extra['addigy_disk_free_space_gb']);
        $this->assertSame('2029-01-15', $records[0]->extra['addigy_warranty_expiration_date']);
    }

    public function test_appledb_extras_carry_values_when_toggle_is_on()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        $this->fakeAddigyAndAppleDb();

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $records[0]->extra['appledb_marketing_name']);
        $this->assertSame('A3123', $records[0]->extra['appledb_model_number']);
        $this->assertSame('MacBookPro18,3', $records[0]->extra['addigy_product_name']);
    }

    private function fakeAddigyAndAppleDb(): void
    {
        Http::fake([
            '*api.addigy.com/api/v2/devices*' => Http::response([
                'items' => [
                    [
                        'agentid' => 'addigy-1',
                        'orgid' => 'org-test',
                        'audit_date' => '2026-01-15T10:00:00Z',
                        'facts' => [
                            'device_name' => ['type' => 'string', 'value' => 'mac-01'],
                            'device_model_name' => ['type' => 'string', 'value' => 'MacBook Pro'],
                            'product_name' => ['type' => 'string', 'value' => 'MacBookPro18,3'],
                            'serial_number' => ['type' => 'string', 'value' => 'ADG-1'],
                            'build_version' => ['type' => 'string', 'value' => '24A5309e'],
                            'udid' => ['type' => 'string', 'value' => 'UDID-ADDIGY-1'],
                            'has_mdm' => ['type' => 'bool', 'value' => true],
                            'filevault_enabled' => ['type' => 'bool', 'value' => true],
                            'firewall_enabled' => ['type' => 'bool', 'value' => false],
                            'total_memory_gb' => ['type' => 'string', 'value' => '16'],
                            'disk_total_space_gb' => ['type' => 'string', 'value' => '500'],
                            'disk_free_space_gb' => ['type' => 'string', 'value' => '250'],
                            'warranty_expiration_date' => ['type' => 'string', 'value' => '2029-01-15'],
                        ],
                    ],
                ],
                'metadata' => ['total' => 1],
            ]),
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

    private function configuredAdapter(bool $enrichmentEnabled): AddigyAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-addigy-api-key'));
        SyncAdapterConfig::put($instance->id, 'use_appledb_enrichment', $enrichmentEnabled ? '1' : '');

        return new AddigyAdapter($instance->fresh());
    }
}
