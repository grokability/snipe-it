<?php

namespace Tests\Feature\SyncAdapters\Mosyle;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Mosyle\MosyleAdapter;
use App\SyncAdapters\PushableAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Mosyle push path. Mosyle's write API
 * is a single POST endpoint dispatched by an `operation` field, so
 * tests verify the payload carries the right operation + serial +
 * value.
 */
class MosylePushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_mosyle_adapter_implements_pushable_interface()
    {
        $this->assertInstanceOf(PushableAdapter::class, $this->configuredMosyle());
    }

    public function test_push_asset_tag_sends_operation_scoped_by_serial()
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
            '*/devices' => Http::response(['status' => 'ok']),
        ]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'POST') {
                return false;
            }
            $body = $request->data();

            return ($body['operation'] ?? null) === 'set_asset_tag_by_serial_number'
                && ($body['serial_number'] ?? null) === 'MOS-SN-777'
                && ($body['asset_tag'] ?? null) === 'SNIPE-MOS-77';
        });
    }

    public function test_push_falls_back_to_external_id_when_serial_missing()
    {
        // Mosyle Business uses the device serial as external_id for
        // some device types, so pushes still work for assets that
        // never populated the serial column but do have an
        // asset_external_sources row.
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
            '*/devices' => Http::response(['status' => 'ok']),
        ]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return ($request->data()['serial_number'] ?? null) === 'MOS-SN-888';
        });
    }

    private function configuredMosyle(): MosyleAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'mosyle')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/mosyle');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-mosyle-token'));

        return new MosyleAdapter($instance->fresh());
    }
}
