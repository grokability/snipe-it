<?php

namespace Tests\Feature\SyncAdapters\WorkspaceOne;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\WorkspaceOne\WorkspaceOneAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Workspace ONE push path. AssetNumber
 * is WS1's native asset_tag equivalent (top-level string field on
 * the device record), so the push shape is a simple JSON PUT.
 */
class WorkspaceOnePushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_workspace_one_adapter_implements_pushable_interface()
    {
        $this->assertInstanceOf(PushableAdapter::class, $this->configuredWs1());
    }

    public function test_push_asset_tag_puts_asset_number_field()
    {
        $adapter = $this->configuredWs1();
        $instance = SyncAdapterInstance::where('slug', 'workspace_one')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'SNIPE-WS1-1234']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'ws1-uuid-42',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/connect/token')) {
                return Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]);
            }
            if ($request->method() === 'PUT' && str_contains($request->url(), '/api/mdm/devices/ws1-uuid-42')) {
                return Http::response(['ok' => true]);
            }

            return Http::response(['unmatched' => true], 500);
        });

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/mdm/devices/ws1-uuid-42')
                && ($request->data()['AssetNumber'] ?? null) === 'SNIPE-WS1-1234';
        });
    }

    private function configuredWs1(): WorkspaceOneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'workspace_one')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/ws1');
        SyncAdapterConfig::put($instance->id, 'tenant_code', 'stub-tenant');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-ws1-secret'));

        return new WorkspaceOneAdapter($instance->fresh());
    }
}
