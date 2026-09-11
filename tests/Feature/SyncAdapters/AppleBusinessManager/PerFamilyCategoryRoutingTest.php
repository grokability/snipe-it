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
use Tests\TestCase;

/**
 * Coverage for the ABM adapter's per-productFamily category routing.
 * ABM knows each device's family (Mac, iPhone, iPad, AppleTV, Watch,
 * Vision) so admins can drop each family into its own Snipe-IT
 * category instead of collapsing everything into the instance's
 * default_category_id. Unset families fall back to the default.
 */
class PerFamilyCategoryRoutingTest extends TestCase
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
    }

    public function test_ipad_routes_to_the_configured_ipad_category_instead_of_default()
    {
        $laptopCategory = Category::factory()->assetLaptopCategory()->create();
        $tabletCategory = Category::factory()->assetTabletCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $laptopCategory->id,
            'category_id_ipad' => (string) $tabletCategory->id,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-ipad', 'attributes' => ['serialNumber' => 'IPADSN', 'partNumber' => 'IPD1', 'productFamily' => 'iPad']],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model = AssetModel::where('name', 'IPD1')->firstOrFail();
        $this->assertSame($tabletCategory->id, $model->category_id);
    }

    public function test_macbook_pro_routes_to_mac_laptop_category()
    {
        $default = Category::factory()->assetDesktopCategory()->create();
        $laptop = Category::factory()->assetLaptopCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            'category_id_mac_laptop' => (string) $laptop->id,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-mbp', 'attributes' => [
                        'serialNumber' => 'MBPSN', 'partNumber' => 'MBP1',
                        'productFamily' => 'Mac',
                        'deviceModel' => 'MacBook Pro (14-inch, M4, 2024)',
                    ]],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model = AssetModel::where('name', 'MBP1')->firstOrFail();
        $this->assertSame($laptop->id, $model->category_id);
    }

    public function test_mac_mini_routes_to_mac_desktop_category()
    {
        $default = Category::factory()->assetLaptopCategory()->create();
        $desktop = Category::factory()->assetDesktopCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            'category_id_mac_desktop' => (string) $desktop->id,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-mini', 'attributes' => [
                        'serialNumber' => 'MINISN', 'partNumber' => 'MINI1',
                        'productFamily' => 'Mac',
                        'deviceModel' => 'Mac mini (M2, 2023)',
                    ]],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model = AssetModel::where('name', 'MINI1')->firstOrFail();
        $this->assertSame($desktop->id, $model->category_id);
    }

    public function test_mac_with_unparseable_marketing_name_falls_through_to_default()
    {
        // Guardrail against a future Apple naming change or a Mac
        // whose deviceModel comes back blank. The framework should
        // fall back to defaultCategoryId rather than lumping the
        // record into an arbitrary bucket.
        $default = Category::factory()->assetLaptopCategory()->create();
        $laptop = Category::factory()->assetLaptopCategory()->create();
        $desktop = Category::factory()->assetDesktopCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            'category_id_mac_laptop' => (string) $laptop->id,
            'category_id_mac_desktop' => (string) $desktop->id,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-mystery', 'attributes' => [
                        'serialNumber' => 'MYSTSN', 'partNumber' => 'MYST1',
                        'productFamily' => 'Mac',
                        'deviceModel' => 'HoloDeck 9000',
                    ]],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model = AssetModel::where('name', 'MYST1')->firstOrFail();
        $this->assertSame($default->id, $model->category_id);
    }

    public function test_existing_model_migrates_to_new_per_family_category_on_next_sync()
    {
        // Simulates: admin ran a sync with only default_category_id
        // set, so all iPads landed in "Laptops". Then admin
        // configures category_id_ipad = Tablets. Next sync must
        // migrate existing iPad models to the newly-mapped category
        // rather than leaving them stuck under the old default.
        $default = Category::factory()->assetLaptopCategory()->create();
        $tablets = Category::factory()->assetTabletCategory()->create();
        $existingModel = AssetModel::factory()->create([
            'name' => 'IPD1',
            'category_id' => $default->id,
        ]);
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            'category_id_ipad' => (string) $tablets->id,
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-ipad', 'attributes' => ['serialNumber' => 'IPADSN', 'partNumber' => 'IPD1', 'productFamily' => 'iPad']],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $existingModel->refresh();
        $this->assertSame($tablets->id, $existingModel->category_id);
    }

    public function test_fall_through_to_default_does_not_overwrite_existing_model_category()
    {
        // Sync must NOT overwrite admin-curated category assignments
        // when the adapter has no per-record opinion. iPhone here
        // has no category_id_iphone override, so categoryIdForRecord
        // returns null and the framework falls through to
        // default_category_id -- but that fall-through is only for
        // fresh model creation, not for re-syncing existing rows.
        $default = Category::factory()->assetLaptopCategory()->create();
        $adminPicked = Category::factory()->assetMobileCategory()->create();
        $existingModel = AssetModel::factory()->create([
            'name' => 'IPH1',
            'category_id' => $adminPicked->id,
        ]);
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            // No category_id_iphone -> adapter has no opinion.
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-iphone', 'attributes' => ['serialNumber' => 'IPHSN', 'partNumber' => 'IPH1', 'productFamily' => 'iPhone']],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $existingModel->refresh();
        $this->assertSame($adminPicked->id, $existingModel->category_id);
    }

    public function test_deleted_configured_category_falls_back_to_default()
    {
        // Guardrail: if the admin picked a category that later got
        // deleted, the framework's category-exists check should force
        // fallback to defaultCategoryId rather than saving a dangling
        // foreign-key-shaped id on the AssetModel.
        $laptopCategory = Category::factory()->assetLaptopCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $laptopCategory->id,
            'category_id_ipad' => '99999999',
        ]);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [
                    ['id' => 'g-ipad', 'attributes' => ['serialNumber' => 'IPADSN', 'partNumber' => 'IPD1', 'productFamily' => 'iPad']],
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $model = AssetModel::where('name', 'IPD1')->firstOrFail();
        $this->assertSame($laptopCategory->id, $model->category_id);
    }

    /**
     * @param  array<string, string|int>  $configOverrides
     */
    private function configuredAdapter(array $configOverrides): AppleBusinessManagerAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mode', 'business');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client-id');
        SyncAdapterConfig::put($instance->id, 'key_id', 'stub-key-id');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));

        foreach ($configOverrides as $key => $value) {
            SyncAdapterConfig::put($instance->id, $key, (string) $value);
        }

        return new AppleBusinessManagerAdapter($instance->fresh());
    }
}
