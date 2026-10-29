<?php

namespace Tests\Feature\SyncAdapters\Levelio;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Levelio\LevelioAdapter;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for the Level.io push path. Level's PATCH
 * /v2/devices/{id} only accepts `name`, `notes`, and `group_id`, and
 * the adapter routes Snipe-IT's composed notes template to `notes`.
 */
class LevelioPushTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_adapter_implements_pushable_interface(): void
    {
        $this->assertInstanceOf(PushableSyncAdapter::class, $this->configuredLevelio());
    }

    public function test_composed_notes_push_sends_patch_to_level(): void
    {
        $adapter = $this->configuredLevelio();
        $instance = SyncAdapterInstance::where('slug', 'levelio')->firstOrFail();

        // Compose template renders the asset tag so the push payload
        // has a non-empty notes body to send.
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Snipe-IT: {{asset_tag}}');

        // Seed via sync so the asset + external-source rows exist.
        Http::fake([
            '*/v2/devices*' => Http::response([
                'data' => [
                    $this->levelDevice(id: 'lvl-uuid-1', hostname: 'notes-host'),
                ],
                'has_more' => false,
            ]),
        ]);
        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $asset = Asset::where('name', 'notes-host')->firstOrFail();
        $asset->asset_tag = 'SNIPE-TAG-42';
        $asset->save();

        Http::fake([
            'PATCH */v2/devices/lvl-uuid-1' => Http::response(['ok' => true]),
        ]);

        $this->assertTrue($adapter->push($asset));

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/v2/devices/lvl-uuid-1')
                && str_contains((string) $request['notes'], 'SNIPE-TAG-42');
        });
    }

    public function test_push_skipped_when_no_composed_notes_template_configured(): void
    {
        $adapter = $this->configuredLevelio();
        $this->seedExternalSource($adapter, 'lvl-uuid-2');

        Http::fake();

        $adapter->push(Asset::first());

        Http::assertNothingSent();
    }

    public function test_push_skipped_when_asset_has_no_external_source_for_this_instance(): void
    {
        $adapter = $this->configuredLevelio();
        $instance = SyncAdapterInstance::where('slug', 'levelio')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Snipe-IT: {{asset_tag}}');

        // Asset with no asset_external_sources row for this instance.
        $asset = Asset::factory()->create();

        Http::fake();

        $adapter->push($asset);

        Http::assertNothingSent();
    }

    public function test_push_sends_api_key_on_authorization_header(): void
    {
        $adapter = $this->configuredLevelio();
        $instance = SyncAdapterInstance::where('slug', 'levelio')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'push_notes_template', 'Snipe-IT: {{asset_tag}}');

        $this->seedExternalSource($adapter, 'lvl-uuid-3');
        $asset = Asset::first();
        $asset->asset_tag = 'SNIPE-TAG-99';
        $asset->save();

        Http::fake(['*' => Http::response(['ok' => true])]);

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && $request->hasHeader('Authorization', 'fake-level-api-key');
        });
    }

    private function configuredLevelio(): LevelioAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'levelio')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://api.level.io');
        SyncAdapterConfig::put($instance->id, 'api_key', Crypt::encrypt('fake-level-api-key'));

        return new LevelioAdapter($instance->fresh());
    }

    private function seedExternalSource(LevelioAdapter $adapter, string $externalId): void
    {
        $asset = Asset::factory()->create();
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => $externalId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function levelDevice(string $id, string $hostname): array
    {
        return [
            'id' => $id,
            'hostname' => $hostname,
            'serial_number' => 'SN-'.$id,
            'manufacturer' => 'Apple Inc.',
            'model' => 'MacBook Pro',
            'platform' => 'Mac',
        ];
    }
}
