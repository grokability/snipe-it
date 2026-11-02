<?php

namespace Tests\Feature\SyncAdapters\Mosyle;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Mosyle\MosyleAdapter;
use App\SyncAdapters\PushableSyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Mosyle push path. Mosyle Manager v2's
 * write API is POST /devices with an `elements` array keyed by
 * `serialnumber`, carrying accessToken in the body and the JWT bearer
 * in the Authorization header. Issue #19790.
 */
class MosylePushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_mosyle_adapter_implements_pushable_interface(): void
    {
        $this->assertInstanceOf(PushableSyncAdapter::class, $this->configuredMosyle());
    }

    public function test_push_asset_tag_sends_elements_array_keyed_by_serial(): void
    {
        $adapter = $this->configuredMosyle();
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create([
            'asset_tag' => 'SNIPE-MOS-77',
            'serial' => 'MOS-SN-777',
        ]);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'mosyle-udid-abc',
        ]);

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response(['status' => 'OK']),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/devices')) {
                return true;
            }
            if ($request->method() !== 'POST') {
                return false;
            }
            $body = $request->data();

            return ($body['accessToken'] ?? null) === 'fake-access-token'
                && ($body['elements'][0]['serialnumber'] ?? null) === 'MOS-SN-777'
                && ($body['elements'][0]['asset_tag'] ?? null) === 'SNIPE-MOS-77'
                && ($request->header('Authorization')[0] ?? null) === 'Bearer fake-jwt';
        });
    }

    public function test_push_falls_back_to_external_id_when_serial_missing(): void
    {
        $adapter = $this->configuredMosyle();
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create([
            'asset_tag' => 'SNIPE-MOS-88',
            'serial' => null,
        ]);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'MOS-SN-888',
        ]);

        Http::fake([
            '*/login' => Http::response(null, 200, ['Authorization' => 'Bearer fake-jwt']),
            '*/devices' => Http::response(['status' => 'OK']),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/devices')) {
                return true;
            }

            return ($request->data()['elements'][0]['serialnumber'] ?? null) === 'MOS-SN-888';
        });
    }

    public function test_notes_field_target_is_null_because_manager_v2_push_has_no_notes(): void
    {
        // Mosyle Manager v2's push payload exposes asset_tag,
        // device_name, lock-screen message, and custom tags. There is
        // no notes field, so the composed-notes framework must skip
        // Mosyle. Regression test for the pre-fix adapter claiming to
        // support notes push via a non-existent operation.
        $this->assertNull($this->configuredMosyle()->notesFieldTarget());
    }

    public function test_mosyle_opts_out_of_composed_notes_push_ui(): void
    {
        // Manager v2 has no field that admin-composed notes could land
        // in, so the settings-page UI section is hidden. notesFieldTarget
        // returning null is not enough on its own because other adapters
        // (Workspace ONE, NinjaOne) legitimately return null and still
        // expect the UI to appear so the admin can pick a target.
        $this->assertFalse($this->configuredMosyle()->supportsComposedNotesPush());
    }

    private function configuredMosyle(): MosyleAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/mosyle');
        SyncAdapterConfig::put($instance->id, 'access_token', Crypt::encrypt('fake-access-token'));
        SyncAdapterConfig::put($instance->id, 'email', 'sync-service@example.com');
        SyncAdapterConfig::put($instance->id, 'password', Crypt::encrypt('fake-password'));

        return new MosyleAdapter($instance->fresh());
    }
}
