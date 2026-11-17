<?php

namespace Tests\Feature\SyncAdapters\Mosyle;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Mosyle\MosyleAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for the Mosyle adapter's per-device-type category routing
 * (issue #19884). Mosyle Manager's device_type is a four-value enum
 * (mac / ios / tvos / visionos) plus a model-prefix split so admins
 * get MacBook vs. iMac/Mini/Studio/Pro and iPhone vs. iPad as
 * separate category selectors. Mirrors the ABM adapter's pattern.
 */
class PerFamilyCategoryRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_ipad_routes_to_the_configured_ipad_category_instead_of_default()
    {
        $laptopCategory = Category::factory()->assetLaptopCategory()->create();
        $tabletCategory = Category::factory()->assetTabletCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $laptopCategory->id,
            'category_id_ipad' => (string) $tabletCategory->id,
        ]);

        $this->fakeMosyleWithDevices([
            // Only the ios OS endpoint returns anything. The other
            // mac/tvos/visionos queries return empty pages.
            'ios' => [$this->mosyleDevice(udid: 'uuid-ipad', model: 'iPad Air', deviceType: 'ios')],
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model = AssetModel::where('name', 'iPad Air')->firstOrFail();
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

        $this->fakeMosyleWithDevices([
            'mac' => [$this->mosyleDevice(udid: 'uuid-mbp', model: 'MacBook Pro', deviceType: 'mac')],
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model = AssetModel::where('name', 'MacBook Pro')->firstOrFail();
        $this->assertSame($laptop->id, $model->category_id);
    }

    public function test_imac_routes_to_mac_desktop_category()
    {
        $default = Category::factory()->assetLaptopCategory()->create();
        $desktop = Category::factory()->assetDesktopCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            'category_id_mac_desktop' => (string) $desktop->id,
        ]);

        $this->fakeMosyleWithDevices([
            'mac' => [$this->mosyleDevice(udid: 'uuid-imac', model: 'iMac', deviceType: 'mac')],
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model = AssetModel::where('name', 'iMac')->firstOrFail();
        $this->assertSame($desktop->id, $model->category_id);
    }

    public function test_apple_tv_falls_back_to_default_when_no_override_set()
    {
        $default = Category::factory()->assetTabletCategory()->create();
        $adapter = $this->configuredAdapter([
            'default_category_id' => $default->id,
            // No category_id_appletv override. Adapter has no opinion,
            // framework uses defaultCategoryId.
        ]);

        $this->fakeMosyleWithDevices([
            'tvos' => [$this->mosyleDevice(udid: 'uuid-atv', model: 'Apple TV 4K', deviceType: 'tvos')],
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $model = AssetModel::where('name', 'Apple TV 4K')->firstOrFail();
        $this->assertSame($default->id, $model->category_id);
    }

    /**
     * Build an Http::fake() that returns the given devices for the
     * matching Mosyle OS and empty pages for every other OS. Mosyle's
     * /listdevices is called once per OS (mac, ios, tvos, visionos),
     * each paginated until an empty page terminates the loop.
     *
     * @param  array<string, list<array<string, mixed>>>  $devicesByOs
     */
    private function fakeMosyleWithDevices(array $devicesByOs): void
    {
        $osOrder = ['mac', 'ios', 'tvos', 'visionos'];
        $sequence = Http::sequence();
        foreach ($osOrder as $os) {
            if (! empty($devicesByOs[$os] ?? [])) {
                $sequence->push($this->devicesResponse($devicesByOs[$os]));
            }
            // Empty terminating page for every OS.
            $sequence->push($this->devicesResponse([]));
        }

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/listdevices' => $sequence,
        ]);
    }

    private function configuredAdapter(array $extraConfig = []): MosyleAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://managerapi.mosyle.com/v2');
        SyncAdapterConfig::put($instance->id, 'access_token', Crypt::encrypt('fake-access-token'));
        SyncAdapterConfig::put($instance->id, 'email', 'sync-service@example.com');
        SyncAdapterConfig::put($instance->id, 'password', Crypt::encrypt('fake-password'));

        foreach ($extraConfig as $key => $value) {
            SyncAdapterConfig::put($instance->id, $key, $value);
        }

        return new MosyleAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function devicesResponse(array $devices): array
    {
        return [
            'status' => 'OK',
            'response' => [
                'devices' => $devices,
                'rows' => (string) count($devices),
                'page_size' => 200,
                'page' => 1,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mosyleDevice(string $udid, string $model, string $deviceType, string $name = 'device'): array
    {
        return [
            'deviceudid' => $udid,
            'device_name' => $name,
            'device_model_name' => $model,
            'serial_number' => 'SN-'.$udid,
            'device_type' => $deviceType,
            'is_supervised' => true,
        ];
    }
}
