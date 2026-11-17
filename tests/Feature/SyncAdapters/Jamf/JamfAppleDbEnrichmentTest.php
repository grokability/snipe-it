<?php

namespace Tests\Feature\SyncAdapters\Jamf;

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Services\AppleDb\AppleDbEnrichment;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JamfAppleDbEnrichmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
        Storage::fake('public');
        AppleDbEnrichment::clearCache();
    }

    public function test_pull_backfills_computer_model_fields_from_appledb()
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

        $this->fakeJamfAndAppleDb(computers: [[
            'id' => '42',
            'udid' => 'jamf-udid-42',
            'general' => ['name' => 'host', 'lastContactTime' => null, 'lastEnrolledDate' => null],
            'hardware' => [
                'make' => 'Apple',
                'model' => 'MacBook Pro',
                'modelIdentifier' => 'MacBookPro18,3',
                'serialNumber' => 'C02-42',
            ],
            'operatingSystem' => ['name' => 'macOS', 'version' => '15.0'],
        ]]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model->refresh();
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $model->mfg_marketing_name);
        $this->assertSame('A3123', $model->model_number);
        $this->assertSame('2024-11-08', $model->mfg_release_date?->toDateString());
        $this->assertSame('2026-03-05', $model->mfg_discontinued_date?->toDateString());
        $this->assertNotNull($model->image);
    }

    public function test_pull_skips_appledb_calls_entirely_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: false);

        $this->fakeJamfAndAppleDb(computers: [[
            'id' => '42',
            'udid' => 'jamf-udid-42',
            'general' => ['name' => 'host', 'lastContactTime' => null, 'lastEnrolledDate' => null],
            'hardware' => [
                'make' => 'Apple',
                'model' => 'MacBook Pro',
                'modelIdentifier' => 'MacBookPro18,3',
                'serialNumber' => 'C02-42',
            ],
            'operatingSystem' => ['name' => 'macOS', 'version' => '15.0'],
        ]]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_mobile_devices_also_resolve_canonical_identity()
    {
        $adapter = $this->configuredAdapter(enrichmentEnabled: true, includeMobile: true);

        $this->fakeJamfAndAppleDb(
            computers: [],
            mobile: [[
                'id' => '7',
                'name' => 'ipad-7',
                'udid' => 'jamf-mobile-udid-7',
                'serialNumber' => 'IPAD-7',
                'assetTag' => null,
                'model' => 'iPad',
                'modelIdentifier' => 'iPad13,2',
                'deviceType' => 'ios',
                'osType' => 'iPadOS',
                'osVersion' => '18.0',
                'wifiMacAddress' => null,
                'ipAddress' => null,
                'lastInventoryUpdateTimestamp' => null,
                'managed' => true,
                'supervised' => false,
                'location' => ['username' => null, 'emailAddress' => null],
                'site' => null,
            ]],
        );

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model = AssetModel::query()->where('name', 'iPad')->first();
        $this->assertNotNull($model);
        $this->assertSame('MacBook Pro (14-inch, M4, 2024)', $model->mfg_marketing_name);
        $this->assertSame('A3123', $model->model_number);
    }

    /**
     * @param  array<int, array<string, mixed>>  $computers
     * @param  array<int, array<string, mixed>>  $mobile
     */
    private function fakeJamfAndAppleDb(array $computers, array $mobile = []): void
    {
        Http::fake([
            'example.jamfcloud.com/api/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]),
            'example.jamfcloud.com/api/v1/computers-inventory*' => Http::response([
                'totalCount' => count($computers),
                'results' => $computers,
            ]),
            'example.jamfcloud.com/api/v2/mobile-devices*' => Http::response([
                'totalCount' => count($mobile),
                'results' => $mobile,
            ]),
            'example.jamfcloud.com/api/v1/sites' => Http::response([]),
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

    private function configuredAdapter(bool $enrichmentEnabled, bool $includeMobile = false): JamfAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.jamfcloud.com');
        SyncAdapterConfig::put($instance->id, 'client_id', Crypt::encrypt('fake-jamf-client-id'));
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-jamf-client-secret'));
        SyncAdapterConfig::put($instance->id, 'include_mobile_devices', $includeMobile ? '1' : '0');
        SyncAdapterConfig::put($instance->id, 'use_appledb_enrichment', $enrichmentEnabled ? '1' : '');

        return new JamfAdapter($instance->fresh());
    }
}
