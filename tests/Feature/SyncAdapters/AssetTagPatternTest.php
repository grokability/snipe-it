<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Coverage for the per-adapter asset-tag pattern feature. Admins
 * configure a pattern like `KANDJI-{serial}` on the adapter's
 * settings tab. SyncHostFromAdapter substitutes placeholders from
 * the HostInventoryRecord when creating new assets. Existing assets
 * keep their tags. the pattern is create-only.
 */
class AssetTagPatternTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_pattern_substitutes_serial_placeholder_on_create()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'asset_tag_pattern', 'FLEET-{serial}');

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: '1',
            hostname: 'wksn-01',
            hardwareSerial: 'SN-ABC123',
            hardwareModel: 'MacBook Pro',
        ));

        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-01',
            'asset_tag' => 'FLEET-SN-ABC123',
        ]);
    }

    public function test_pattern_substitutes_multiple_placeholders()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'asset_tag_pattern', '{source}-{external_id}');

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'host-99',
            hostname: 'wksn-02',
            hardwareSerial: 'SN-XYZ',
            hardwareModel: 'MacBook Pro',
        ));

        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-02',
            'asset_tag' => 'fleet-host-99',
        ]);
    }

    public function test_pattern_with_missing_placeholder_still_renders_when_other_data_present()
    {
        // Vendor sent no serial. pattern substitutes empty. Result is
        // still a usable tag because the literal prefix survives.
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'asset_tag_pattern', 'FLEET-{external_id}-{serial}');

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'h1',
            hostname: 'wksn-03',
            hardwareSerial: null,
            hardwareModel: 'MacBook Pro',
        ));

        // {serial} rendered empty, so the trailing dash stays. Ugly
        // but predictable. admin can adjust the pattern.
        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-03',
            'asset_tag' => 'FLEET-h1-',
        ]);
    }

    public function test_no_pattern_falls_back_to_synthetic_source_id_tag()
    {
        $this->configuredFleet();
        // No asset_tag_pattern stored.

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'h42',
            hostname: 'wksn-04',
            hardwareSerial: 'SN-DEF',
            hardwareModel: 'MacBook Pro',
        ));

        // With no pattern and no Snipe-IT autoincrement setting active
        // in this test, the fallback synthetic tag ({source}-{sourceId})
        // wins.
        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-04',
            'asset_tag' => 'fleet-h42',
        ]);
    }

    public function test_pattern_does_not_retag_existing_asset_on_update_sync()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'asset_tag_pattern', 'FLEET-{serial}');

        // First sync creates the asset.
        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'stable-1',
            hostname: 'wksn-05',
            hardwareSerial: 'SN-ORIG',
            hardwareModel: 'MacBook Pro',
        ));

        $created = Asset::where('name', 'wksn-05')->firstOrFail();
        $this->assertSame('FLEET-SN-ORIG', $created->asset_tag);

        // Admin manually renames the tag through the UI.
        $created->update(['asset_tag' => 'CURATED-TAG-123']);

        // Change the pattern between syncs (or the serial in the
        // vendor payload). Second sync of the SAME external_id should
        // not touch asset_tag.
        SyncAdapterConfig::put($fleet->id, 'asset_tag_pattern', 'DIFFERENT-{serial}');

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'stable-1',
            hostname: 'wksn-05',
            hardwareSerial: 'SN-NEW',
            hardwareModel: 'MacBook Pro',
        ));

        $created->refresh();
        $this->assertSame('CURATED-TAG-123', $created->asset_tag);
    }

    public function test_default_category_id_places_auto_created_models_in_configured_category()
    {
        $fleet = $this->configuredFleet();
        $category = \App\Models\Category::factory()->assetLaptopCategory()->create();
        SyncAdapterConfig::put($fleet->id, 'default_category_id', (string) $category->id);

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'cat-1',
            hostname: 'wksn-cat',
            hardwareSerial: 'SN-CAT',
            hardwareModel: 'A-New-Model-For-This-Test',
        ));

        $model = \App\Models\AssetModel::where('name', 'A-New-Model-For-This-Test')->firstOrFail();
        $this->assertSame($category->id, (int) $model->category_id);
    }

    public function test_default_category_falls_back_to_discovered_hardware_when_unset()
    {
        $this->configuredFleet();
        // No default_category_id stored.

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'cat-2',
            hostname: 'wksn-discovered',
            hardwareSerial: 'SN-DIS',
            hardwareModel: 'Another-New-Model-For-This-Test',
        ));

        $model = \App\Models\AssetModel::where('name', 'Another-New-Model-For-This-Test')->firstOrFail();
        $fallback = \App\Models\Category::where('name', 'Discovered Hardware')->firstOrFail();
        $this->assertSame($fallback->id, (int) $model->category_id);
    }

    public function test_pattern_field_persists_on_save()
    {
        $fleet = $this->configuredFleet();

        $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), [
                'fleet_url' => 'https://example.com/fleet',
                'fleet_token' => 'fake-token',
                'fleet_asset_tag_pattern' => 'FLEET-{serial}',
                'fleet_default_category_id' => Category::factory()->create()->id,
                'fleet_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect();

        $this->assertSame(
            'FLEET-{serial}',
            SyncAdapterConfig::get($fleet->id, 'asset_tag_pattern'),
        );
    }

    private function configuredFleet(): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/fleet');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return $instance->fresh();
    }
}
