<?php

namespace Tests\Feature\SyncAdapters\Jamf;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\PushableSyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Jamf Pro push path. Verifies the
 * PushableSyncAdapter interface, the PATCH payload shape (asset_tag
 * writes to general.assetTag per Jamf's ComputerGeneralUpdate
 * schema), and dry-run behavior.
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
        $this->assertInstanceOf(PushableSyncAdapter::class, $this->configuredJamf());
    }

    public function test_push_asset_tag_writes_to_general_asset_tag_path()
    {
        // Regression pin for issue #19851. Jamf Pro's PATCH /api/v1/
        // computers-inventory-detail accepts assetTag only on the
        // `general` object (ComputerGeneralUpdate), never on
        // `userAndLocation`. A push to the wrong path would be a
        // silent no-op against a real tenant.
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
            '*/api/oauth/token' => Http::response([
                'access_token' => 'fake-jamf-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]),
            '*/api/v1/computers-inventory-detail/*' => Http::response(['ok' => true]),
        ]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/api/v1/computers-inventory-detail/jamf-computer-id-42')
                && ($request->data()['general']['assetTag'] ?? null) === 'SNIPE-JAMF-42'
                && ! isset($request->data()['userAndLocation']['assetTag']);
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
        SyncAdapterConfig::put($instance->id, 'client_id', Crypt::encrypt('fake-jamf-client-id'));
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-jamf-client-secret'));

        return new JamfAdapter($instance->fresh());
    }
}
