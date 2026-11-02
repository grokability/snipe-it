<?php

namespace Tests\Feature\SyncAdapters\JamfSchool;

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Services\AppleDb\AppleDbEnrichment;
use App\SyncAdapters\JamfSchool\JamfSchoolAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JamfSchoolAppleDbEnrichmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
        Storage::fake('public');
        AppleDbEnrichment::clearCache();
    }

    public function test_pull_backfills_model_fields_from_appledb()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        $model = AssetModel::factory()->create([
            'name' => 'iPad',
            'image' => null,
            'model_number' => null,
            'mfg_marketing_name' => null,
            'mfg_release_date' => null,
            'mfg_discontinued_date' => null,
        ]);

        $this->fakeJamfSchoolAndAppleDb();

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model->refresh();
        $this->assertSame('iPad Pro (11-inch, 4th generation)', $model->mfg_marketing_name);
        $this->assertSame('A2759', $model->model_number);
        $this->assertSame('2022-10-26', $model->mfg_release_date?->toDateString());
        $this->assertNotNull($model->image);
    }

    public function test_pull_skips_appledb_entirely_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);
        $this->fakeJamfSchoolAndAppleDb();

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_record_extras_carry_appledb_values_when_toggle_is_on()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true);
        $this->fakeJamfSchoolAndAppleDb();

        $records = iterator_to_array($adapter->pull());

        $this->assertSame('iPad Pro (11-inch, 4th generation)', $records[0]->extra['appledb_marketing_name']);
        $this->assertSame('A2759', $records[0]->extra['appledb_model_number']);
        $this->assertSame('2022-10-26', $records[0]->extra['appledb_release_date']);
    }

    public function test_extra_fields_declaration_hides_appledb_keys_when_toggle_is_off()
    {
        $disabled = $this->configuredAdapter(enrichmentEnabled: false);
        $this->assertArrayNotHasKey('appledb_marketing_name', $disabled->extraFields());

        $enabled = $this->configuredAdapter(enrichmentEnabled: true);
        $this->assertArrayHasKey('appledb_marketing_name', $enabled->extraFields());
    }

    private function fakeJamfSchoolAndAppleDb(): void
    {
        Http::fake([
            '*/devices*' => Http::response([
                'count' => 1,
                'devices' => [[
                    'UDID' => 'edu-udid-7',
                    'name' => 'ipad-lab',
                    'serialNumber' => 'JS-7',
                    'model' => [
                        'name' => 'iPad',
                        'identifier' => 'iPad14,3',
                    ],
                    'os' => ['prefix' => 'iPadOS', 'version' => '17.0'],
                    'networkInformation' => ['WiFiMAC' => null, 'IPAddress' => null],
                    'lastCheckin' => null,
                    'isSupervised' => true,
                    'assetTag' => null,
                    'locationId' => 1,
                ]],
            ]),
            'api.appledb.dev/device/*' => Http::response([
                'name' => 'iPad Pro (11-inch, 4th generation)',
                'released' => '2022-10-26',
                'model' => ['A2759'],
                'imageKey' => 'ipad-pro-11-m2',
                'colors' => [['key' => 'silver']],
            ]),
            'img.appledb.dev/*' => Http::response(str_repeat("\x89PNG\r\n\x1a\n", 4)),
        ]);
    }

    private function configuredAdapter(bool $enrichmentEnabled): JamfSchoolAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jamf_school')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.jamfcloud.com/api');
        SyncAdapterConfig::put($instance->id, 'network_id', 'school-network-id');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-jamf-school-key'));
        SyncAdapterConfig::put($instance->id, 'use_appledb_enrichment', $enrichmentEnabled ? '1' : '');

        return new JamfSchoolAdapter($instance->fresh());
    }
}
