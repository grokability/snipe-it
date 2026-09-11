<?php

namespace Tests\Feature\SyncAdapters\NinjaOne;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\NinjaOne\NinjaOneAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for NinjaOne composed-notes push. Ninja has no built-in
 * notes column, so admins point the composed notes at an admin-named
 * Custom Field via the push_notes_target override. Tests verify the
 * PATCH lands with both asset_tag AND composed notes in the same
 * /custom-fields call.
 */
class NinjaOneComposedNotesPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_push_writes_asset_tag_and_composed_notes_in_one_patch()
    {
        $instance = SyncAdapterInstance::where('slug', 'ninjaone')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/ninja');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('secret'));
        SyncAdapterConfig::put($instance->id, 'asset_tag_custom_field', 'snipeAssetTag');
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Tag: {asset_tag}');
        SyncAdapterConfig::put($instance->id, 'push_notes_target', 'snipeInfo');

        $adapter = new NinjaOneAdapter($instance->fresh());

        $asset = Asset::factory()->create(['asset_tag' => 'NIN-42']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => '42',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/ws/oauth/token')) {
                return Http::response(['access_token' => 'stub', 'expires_in' => 3600]);
            }
            if ($request->method() === 'PATCH' && str_contains($request->url(), '/v2/device/42/custom-fields')) {
                return Http::response(['ok' => true]);
            }

            return Http::response(['unmatched' => true], 500);
        });

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->method() === 'PATCH'
                && ($body['snipeAssetTag'] ?? null) === 'NIN-42'
                && ($body['snipeInfo'] ?? null) === 'Tag: NIN-42';
        });
    }

    public function test_composed_notes_no_op_when_target_field_blank()
    {
        // No push_notes_target set. Even with a template, we don't
        // guess a field name and skip silently. asset_tag push still
        // fires because it has its own dedicated slot.
        $instance = SyncAdapterInstance::where('slug', 'ninjaone')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/ninja');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('secret'));
        SyncAdapterConfig::put($instance->id, 'asset_tag_custom_field', 'snipeAssetTag');
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Tag: {asset_tag}');

        $adapter = new NinjaOneAdapter($instance->fresh());

        $asset = Asset::factory()->create(['asset_tag' => 'NIN-77']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => '77',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/ws/oauth/token')) {
                return Http::response(['access_token' => 'stub', 'expires_in' => 3600]);
            }

            return Http::response(['ok' => true]);
        });

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->method() === 'PATCH'
                && ($body['snipeAssetTag'] ?? null) === 'NIN-77'
                && ! array_key_exists('snipeInfo', $body ?? []);
        });
    }
}
