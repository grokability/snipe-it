<?php

namespace Tests\Feature\SyncAdapters\Jamf;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\PushableAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Jamf Pro push path. Verifies the
 * PushableAdapter interface, the PATCH payload shape (nested
 * userAndLocation.assetTag path per Jamf's inventory-detail JSON),
 * and dry-run behavior.
 */
class JamfPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_jamf_adapter_implements_pushable_interface()
    {
        $this->assertInstanceOf(PushableAdapter::class, $this->configuredJamf());
    }

    public function test_push_asset_tag_sends_patch_with_nested_user_and_location_path()
    {
        $adapter = $this->configuredJamf();
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create([
            'asset_tag' => 'SNIPE-JAMF-42',
        ]);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'jamf-computer-id-42',
        ]);

        Http::fake([
            '*/api/v1/computers-inventory-detail/*' => Http::response(['ok' => true]),
        ]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/api/v1/computers-inventory-detail/jamf-computer-id-42')
                && ($request->data()['userAndLocation']['assetTag'] ?? null) === 'SNIPE-JAMF-42';
        });
    }

    public function test_push_dry_run_logs_instead_of_sending()
    {
        $adapter = $this->configuredJamf();
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');
        SyncAdapterConfig::put($instance->id, 'push_dry_run', '1');

        $asset = Asset::factory()->create(['asset_tag' => 'DRY-RUN-JAMF']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'jamf-99',
        ]);

        Http::fake();

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    public function test_push_skipped_when_asset_has_no_external_source()
    {
        $adapter = $this->configuredJamf();
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'NO-SOURCE-JAMF']);

        Http::fake();

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    private function configuredJamf(): JamfAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/jamf');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-jamf-token'));

        return new JamfAdapter($instance->fresh());
    }
}
