<?php

namespace Tests\Feature\SyncAdapters\WorkspaceOne;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\WorkspaceOne\WorkspaceOneAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for Workspace ONE composed-notes push. WS1 has no built-in
 * device-level notes field, so composed notes get written to an
 * admin-named Custom Attribute via a separate POST endpoint
 * (/customattributes) from the top-level AssetNumber PUT.
 */
class WorkspaceOneComposedNotesPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_push_fires_both_endpoints_when_asset_tag_and_notes_configured()
    {
        $instance = SyncAdapterInstance::where('slug', 'workspace_one')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/ws1');
        SyncAdapterConfig::put($instance->id, 'tenant_code', 'stub');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('secret'));
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Tag: {asset_tag}');
        SyncAdapterConfig::put($instance->id, 'push_notes_target', 'SnipeInfo');

        $adapter = new WorkspaceOneAdapter($instance->fresh());

        $asset = Asset::factory()->create(['asset_tag' => 'WS1-42']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ws1-uuid-42',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/connect/token')) {
                return Http::response(['access_token' => 'stub', 'expires_in' => 3600]);
            }

            return Http::response(['ok' => true]);
        });

        $adapter->push($asset);

        // Top-level device PUT for AssetNumber.
        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/mdm/devices/ws1-uuid-42')
                && ! str_contains($request->url(), 'customattributes')
                && ($request->data()['AssetNumber'] ?? null) === 'WS1-42';
        });

        // Separate POST for the Custom Attribute.
        Http::assertSent(function ($request) {
            if ($request->method() !== 'POST') {
                return false;
            }
            if (! str_contains($request->url(), '/api/mdm/devices/ws1-uuid-42/customattributes')) {
                return false;
            }
            $attr = $request->data()['CustomAttributes'][0] ?? null;

            return $attr !== null
                && $attr['Name'] === 'SnipeInfo'
                && $attr['Value'] === 'Tag: WS1-42';
        });
    }

    public function test_composed_notes_only_skips_the_top_level_put()
    {
        $instance = SyncAdapterInstance::where('slug', 'workspace_one')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/ws1');
        SyncAdapterConfig::put($instance->id, 'tenant_code', 'stub');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('secret'));
        // No direction.asset_tag = 'push' -> AssetNumber not pushed
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Just notes');
        SyncAdapterConfig::put($instance->id, 'push_notes_target', 'SnipeInfo');

        $adapter = new WorkspaceOneAdapter($instance->fresh());

        $asset = Asset::factory()->create(['asset_tag' => 'WS1-88']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ws1-uuid-88',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/connect/token')) {
                return Http::response(['access_token' => 'stub', 'expires_in' => 3600]);
            }

            return Http::response(['ok' => true]);
        });

        $adapter->push($asset);

        Http::assertNotSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/mdm/devices/ws1-uuid-88')
                && ! str_contains($request->url(), 'customattributes');
        });
        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/api/mdm/devices/ws1-uuid-88/customattributes');
        });
    }
}
