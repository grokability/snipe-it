<?php

namespace Tests\Feature\SyncAdapters\NinjaOne;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\NinjaOne\NinjaOneAdapter;
use App\SyncAdapters\PushableAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the NinjaOne push path. NinjaOne has no
 * native asset_tag field, so push writes to an admin-configured
 * per-device Custom Field. Tests verify the payload shape, the
 * skip-when-unconfigured path, and the correct field name lands in
 * the PATCH body.
 */
class NinjaOnePushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_ninjaone_adapter_implements_pushable_interface()
    {
        $this->assertInstanceOf(PushableAdapter::class, $this->configuredNinja());
    }

    public function test_push_asset_tag_writes_to_configured_custom_field()
    {
        $adapter = $this->configuredNinja(customFieldName: 'snipeAssetTag');
        $instance = SyncAdapterInstance::where('slug', 'ninjaone')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'SNIPE-NIN-1234']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => '42',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/ws/oauth/token')) {
                return Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]);
            }
            if ($request->method() === 'PATCH' && str_contains($request->url(), '/v2/device/42/custom-fields')) {
                return Http::response(['ok' => true]);
            }

            return Http::response(['unmatched' => true], 500);
        });

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/v2/device/42/custom-fields')
                && ($request->data()['snipeAssetTag'] ?? null) === 'SNIPE-NIN-1234';
        });
    }

    public function test_push_skips_when_custom_field_name_is_blank()
    {
        // Ninja has no native asset_tag, so with no custom field
        // configured there's nothing to push to. Silently skip
        // with an info-level log rather than error out.
        $adapter = $this->configuredNinja(customFieldName: null);
        $instance = SyncAdapterInstance::where('slug', 'ninjaone')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'SNIPE-NIN-NO-FIELD']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => '99',
        ]);

        Http::fake();

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    private function configuredNinja(?string $customFieldName = null): NinjaOneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'ninjaone')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/ninja');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-ninja-secret'));
        if ($customFieldName !== null) {
            SyncAdapterConfig::put($instance->id, 'asset_tag_custom_field', $customFieldName);
        }

        return new NinjaOneAdapter($instance->fresh());
    }
}
