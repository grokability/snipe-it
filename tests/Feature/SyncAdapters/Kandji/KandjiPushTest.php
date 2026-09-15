<?php

namespace Tests\Feature\SyncAdapters\Kandji;

use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Kandji\KandjiAdapter;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Kandji push path. Verifies the mapping
 * table's direction column, PushableAdapter dispatch, and the actual
 * PATCH shape (form-encoded asset_tag) that goes to Kandji.
 */
class KandjiPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_kandji_adapter_implements_pushable_interface()
    {
        $adapter = $this->configuredKandji();

        $this->assertInstanceOf(PushableAdapter::class, $adapter);
    }

    public function test_push_asset_tag_sends_patch_to_kandji()
    {
        $adapter = $this->configuredKandji();
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', 'kandji')->firstOrFail()->id, 'direction.asset_tag', 'push');

        // Seed an asset + external-source row so push() has a device
        // id to write against. Using the sync path to seed keeps the
        // shape realistic.
        Http::fake([
            '*/api/v1/devices*' => Http::response([
                $this->kandjiDevice(deviceId: 'kandji-uuid-1', name: 'test-host'),
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $asset = \App\Models\Asset::where('name', 'test-host')->firstOrFail();
        $asset->asset_tag = 'SNIPE-EDIT-1234';
        $asset->save();

        // Fresh fake for the push PATCH.
        Http::fake([
            'PATCH */api/v1/devices/kandji-uuid-1' => Http::response(['ok' => true]),
        ]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/api/v1/devices/kandji-uuid-1')
                && $request['asset_tag'] === 'SNIPE-EDIT-1234';
        });
    }

    public function test_push_skipped_when_no_field_is_push_directed()
    {
        $adapter = $this->configuredKandji();
        // Explicitly leave direction unset. Defaults to 'pull'.
        $this->seedExternalSource($adapter, 'kandji-uuid-2');

        Http::fake();

        $adapter->push(\App\Models\Asset::first());

        Http::assertNothingSent();
    }

    public function test_push_skipped_when_asset_has_no_external_source_for_this_instance()
    {
        $adapter = $this->configuredKandji();
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', 'kandji')->firstOrFail()->id, 'direction.asset_tag', 'push');

        // Asset with no asset_external_sources row for this instance.
        $asset = \App\Models\Asset::factory()->create();

        Http::fake();

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    public function test_pull_path_does_not_overwrite_asset_tag_when_directed_push()
    {
        $adapter = $this->configuredKandji();
        SyncAdapterConfig::put(SyncAdapterInstance::where('slug', 'kandji')->firstOrFail()->id, 'direction.asset_tag', 'push');

        Http::fake([
            '*/api/v1/devices*' => Http::response([
                $this->kandjiDevice(deviceId: 'kandji-uuid-3', name: 'flip-host', assetTag: 'VENDOR-SIDE-TAG'),
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $asset = \App\Models\Asset::where('name', 'flip-host')->firstOrFail();
        // asset_tag came from the auto-tag path (autoincrement or
        // adapter pattern) rather than the vendor's asset_tag because
        // direction.asset_tag is 'push', not 'pull'.
        $this->assertNotSame('VENDOR-SIDE-TAG', $asset->asset_tag);
    }

    private function configuredKandji(): KandjiAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'kandji')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/kandji');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-kandji-token'));

        return new KandjiAdapter($instance->fresh());
    }

    private function seedExternalSource(KandjiAdapter $adapter, string $externalId): void
    {
        $asset = \App\Models\Asset::factory()->create();
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => $externalId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function kandjiDevice(string $deviceId, string $name, ?string $assetTag = null): array
    {
        return [
            'device_id' => $deviceId,
            'device_name' => $name,
            'serial_number' => 'SN-'.$deviceId,
            'model' => 'MacBook Pro',
            'platform' => 'Mac',
            'asset_tag' => $assetTag,
        ];
    }
}
