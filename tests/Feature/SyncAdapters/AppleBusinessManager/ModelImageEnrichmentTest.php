<?php

namespace Tests\Feature\SyncAdapters\AppleBusinessManager;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\AppleBusinessManager\AppleBusinessManagerAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Coverage for the ABM model-image enrichment path. Fetches from
 * appledb.dev when the toggle is on, respects existing images on
 * model + category, and no-ops entirely when the toggle is off.
 */
class ModelImageEnrichmentTest extends TestCase
{
    private string $privateKeyPem;

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        $pem = '';
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;

        Storage::fake('public');
    }

    public function test_pull_backfills_model_image_when_toggle_is_on_and_model_lacks_one()
    {
        $adapter = $this->configuredAdapter(pullImages: true);
        // Pre-create the model so backfill has something to update.
        $category = Category::factory()->assetLaptopCategory()->create(['image' => null]);
        $model = AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => null,
            'category_id' => $category->id,
        ]);

        $this->fakeAppleDbAndAbm();

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model->refresh();
        $this->assertNotNull($model->image);
        Storage::disk('public')->assertExists('models/'.$model->image);
    }

    public function test_pull_does_not_touch_existing_model_image()
    {
        $adapter = $this->configuredAdapter(pullImages: true);
        $model = AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => 'admin-uploaded.png',
        ]);

        $this->fakeAppleDbAndAbm();

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model->refresh();
        $this->assertSame('admin-uploaded.png', $model->image);
    }

    public function test_pull_still_backfills_model_image_when_category_has_its_own_image()
    {
        // The category-image shortcut used to block per-model images
        // entirely when the category carried a fallback icon, which
        // meant admins with curated category icons never got Apple's
        // marketing shots on individual models. Now the category
        // image only serves as a fallback in AssetModel::getImageUrl;
        // per-model images still populate on top.
        $adapter = $this->configuredAdapter(pullImages: true);
        $category = Category::factory()->assetLaptopCategory()->create(['image' => 'category-image.png']);
        $model = AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => null,
            'category_id' => $category->id,
        ]);

        $this->fakeAppleDbAndAbm();

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model->refresh();
        $this->assertNotNull($model->image);
    }

    public function test_pull_skips_appledb_calls_entirely_when_toggle_is_off()
    {
        $adapter = $this->configuredAdapter(pullImages: false);

        $this->fakeAppleDbAndAbm();

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'appledb.dev'));
    }

    public function test_first_sync_stages_image_to_disk_even_when_model_row_does_not_exist_yet()
    {
        // Timing gap: ensureModelImage runs inside pull(), before
        // SyncHostFromAdapter has created the AssetModel row. First
        // sync must still hit appledb.dev and cache the PNG so the
        // second sync only needs to assign existing->image (no
        // re-download) once the framework creates the row.
        $adapter = $this->configuredAdapter(pullImages: true);
        $this->assertSame(0, AssetModel::query()->count());

        // Wildcard on the device endpoint sidesteps rawurlencode
        // turning the comma in "MacBookPro18,3" into "%2C" (which
        // the literal Http::fake pattern would miss). Http::fake
        // doesn't preventStrayRequests, so a missed pattern silently
        // falls through to the real internet, defeating hermeticity.
        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->deviceWithMarketingName()],
            ]),
            'api.appledb.dev/device/*' => Http::response([
                'imageKey' => 'macbook-pro-14-m4',
                'colors' => [['key' => 'silver']],
            ]),
            'img.appledb.dev/*' => Http::response(str_repeat("\x89PNG\r\n\x1a\n", 4)),
        ]);

        // Consume records without running SyncHostFromAdapter so no
        // AssetModel gets created. Simulates "framework hasn't built
        // the row yet" at the point ensureModelImage runs.
        iterator_to_array($adapter->pull());

        // File landed on disk keyed off (productType, colorKey).
        // AppleDBImageFetcher::filenameFor uses sha1(productType|colorKey)[0:16].
        $expectedFilename = 'abm-'.substr(hash('sha1', 'MacBookPro18,3|silver'), 0, 16).'.png';
        Storage::disk('public')->assertExists('models/'.$expectedFilename);

        // No model was created (pull() alone doesn't build rows), so
        // nothing to assert on the AssetModel side.
        $this->assertSame(0, AssetModel::query()->count());
    }

    public function test_pull_survives_appledb_outage()
    {
        $adapter = $this->configuredAdapter(pullImages: true);
        $model = AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => null,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->deviceWithMarketingName()],
            ]),
            'api.appledb.dev/*' => Http::response(['error' => 'boom'], 503),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model->refresh();
        // No image populated, but sync completed without throwing.
        $this->assertNull($model->image);
    }

    public function test_pull_only_fetches_once_per_hardware_model_across_many_devices()
    {
        $adapter = $this->configuredAdapter(pullImages: true);
        AssetModel::factory()->create([
            'name' => 'MacBook Pro (14-inch, M4, 2024)',
            'image' => null,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => array_map(
                    fn (int $i) => $this->deviceWithMarketingName(id: 'guid-'.$i),
                    range(1, 5),
                ),
            ]),
            'api.appledb.dev/device/MacBookPro18,3.json' => Http::response([
                'imageKey' => 'macbook-pro-14-m4',
                'colors' => [['key' => 'silver']],
            ]),
            'img.appledb.dev/*' => Http::response(str_repeat("\x89PNG\r\n\x1a\n", 4)),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        // Even with 5 devices sharing the same hardwareModel, we hit
        // appledb.dev exactly once (info + image).
        $infoCount = 0;
        $imgCount = 0;
        Http::assertSent(function ($request) use (&$infoCount, &$imgCount) {
            if (str_contains($request->url(), 'api.appledb.dev')) {
                $infoCount++;
            }
            if (str_contains($request->url(), 'img.appledb.dev')) {
                $imgCount++;
            }

            return true;
        });
        $this->assertSame(1, $infoCount);
        $this->assertSame(1, $imgCount);
    }

    private function fakeAppleDbAndAbm(): void
    {
        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->deviceWithMarketingName()],
            ]),
            'api.appledb.dev/device/MacBookPro18,3.json' => Http::response([
                'imageKey' => 'macbook-pro-14-m4',
                'colors' => [['key' => 'silver'], ['key' => 'space-black']],
            ]),
            'img.appledb.dev/*' => Http::response(str_repeat("\x89PNG\r\n\x1a\n", 4)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function deviceWithMarketingName(string $id = 'abm-guid-1'): array
    {
        return [
            'id' => $id,
            'attributes' => [
                'serialNumber' => 'C02SN-'.$id,
                'partNumber' => 'MK1E3LL/A',
                'productType' => 'MacBookPro18,3',
                'deviceModel' => 'MacBook Pro (14-inch, M4, 2024)',
                'productFamily' => 'Mac',
                'color' => 'silver',
            ],
        ];
    }

    private function configuredAdapter(bool $pullImages): AppleBusinessManagerAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mode', 'business');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client-id');
        SyncAdapterConfig::put($instance->id, 'key_id', 'stub-key-id');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));
        SyncAdapterConfig::put($instance->id, 'pull_model_images', $pullImages ? '1' : '');
        // Route the marketing name to native:model so the framework
        // uses it as the AssetModel::name (which the pre-created
        // model in each test is named after).
        SyncAdapterConfig::put($instance->id, 'mapping.abm_model_marketing_name', 'native:model');

        return new AppleBusinessManagerAdapter($instance->fresh());
    }
}
