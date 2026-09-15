<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Fleet\FleetAdapter;
use App\SyncAdapters\Support\MappingTargets;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for the extra-field mapping flow: adapters declare
 * vendor-specific extra keys via extraFields(), admin maps each to a
 * custom field, and SyncHostFromAdapter writes the (stringified) value
 * onto the asset's dynamic column.
 *
 * Fleet is the workhorse here because its normalize() emits a mix of
 * scalar (fleet_team, fleet_uuid, fleet_status) and array
 * (fleet_labels) values, exercising the stringifier for both shapes.
 */
class ExtraFieldMappingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_extra_field_mapping_writes_scalar_value_to_custom_field()
    {
        $customField = CustomField::factory()->create(['element' => 'text']);
        $fleet = $this->configuredFleetInstance();

        // Map fleet_team (scalar string) to the custom field.
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'custom:'.$customField->id);

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push(['hosts' => [
                    $this->fleetHost(id: 1, hostname: 'wksn-01', hardware_model: 'MacBook Pro', team_name: 'Engineering'),
                ]])
                ->push(['hosts' => []]),
        ]);

        $adapter = new FleetAdapter($fleet);
        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-01',
            $customField->db_column => 'Engineering',
        ]);
    }

    public function test_extra_field_mapping_stringifies_array_to_comma_joined()
    {
        $customField = CustomField::factory()->create(['element' => 'text']);
        $fleet = $this->configuredFleetInstance();

        // Map fleet_labels (an array from the vendor) to the custom field.
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_labels', 'custom:'.$customField->id);

        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push(['hosts' => [
                    $this->fleetHost(
                        id: 2,
                        hostname: 'wksn-02',
                        hardware_model: 'MacBook Pro',
                        labels: ['production', 'us-east', 'engineering'],
                    ),
                ]])
                ->push(['hosts' => []]),
        ]);

        $adapter = new FleetAdapter($fleet);
        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-02',
            $customField->db_column => 'production, us-east, engineering',
        ]);
    }

    public function test_skipped_extra_fields_do_not_touch_the_asset()
    {
        $customField = CustomField::factory()->create(['element' => 'text']);
        $fleet = $this->configuredFleetInstance();

        // No mapping stored: default is skip for extras.
        Http::fake([
            '*/api/latest/fleet/hosts*' => Http::sequence()
                ->push(['hosts' => [
                    $this->fleetHost(id: 3, hostname: 'wksn-03', hardware_model: 'MacBook Pro', team_name: 'Engineering'),
                ]])
                ->push(['hosts' => []]),
        ]);

        $adapter = new FleetAdapter($fleet);
        foreach ($adapter->pull() as $record) {
            SyncHostFromAdapter::run($record);
        }

        // Asset created, but the custom-field column stays null since
        // no mapping was stored.
        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-03',
            $customField->db_column => null,
        ]);
    }

    public function test_extra_field_options_include_skip_native_and_custom_targets_for_text_type()
    {
        CustomField::factory()->create(['element' => 'text', 'name' => 'Team Assignment']);

        $options = MappingTargets::optionsForExtra('text');

        $this->assertArrayHasKey('skip', $options);

        // Text-type extras can route to native asset_tag / model /
        // notes so admins whose vendor stores per-device metadata in
        // labels / blueprints / teams / marketing names can land it
        // in a native column instead of forcing a custom field.
        // Everything else must be custom:{id}.
        $this->assertArrayHasKey('native:asset_tag', $options);
        $this->assertArrayHasKey('native:model', $options);
        $this->assertArrayHasKey('native:notes', $options);
        foreach (array_keys($options) as $target) {
            if (in_array($target, ['skip', 'native:asset_tag', 'native:model', 'native:notes'], true)) {
                continue;
            }
            $this->assertStringStartsWith('custom:', $target);
        }
    }

    public function test_boolean_extra_field_options_stay_custom_only()
    {
        CustomField::factory()->create(['element' => 'checkbox', 'name' => 'Flagged']);

        $options = MappingTargets::optionsForExtra('boolean');

        // Boolean-typed extras stay custom-fields-only because no
        // native asset column carries a boolean semantic.
        $this->assertArrayHasKey('skip', $options);
        $this->assertArrayNotHasKey('native:asset_tag', $options);
        $this->assertArrayNotHasKey('native:model', $options);
        $this->assertArrayNotHasKey('native:notes', $options);
    }

    public function test_asset_tag_native_target_overwrites_the_asset_tag_column()
    {
        $fleet = $this->configuredFleetInstance();

        // Standard-field mapping: route the normalized asset_tag field
        // to Snipe-IT's native asset_tag column. Fleet doesn't emit
        // asset_tag in its normalize() so we simulate a source that
        // does by using the extra->normalized path elsewhere. Here we
        // exercise the mapping wiring itself.
        SyncAdapterConfig::put($fleet->id, 'mapping.asset_tag', 'native:asset_tag');

        // Build a record with an explicit assetTag so writeNative
        // picks it up.
        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'tag-test-1',
            hostname: 'host-with-tag',
            hardwareSerial: 'SN-1',
            hardwareModel: 'MacBook Pro',
            assetTag: 'VENDOR-TAG-001',
        );

        SyncHostFromAdapter::run($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-with-tag',
            'asset_tag' => 'VENDOR-TAG-001',
        ]);
    }

    public function test_asset_tag_defaults_to_native_and_writes_vendor_tag()
    {
        $this->configuredFleetInstance();

        // No explicit mapping stored: default target for asset_tag is
        // native:asset_tag so the vendor's tag flows into Snipe-IT's
        // asset_tag column. Common case is admins want the printed
        // sticker in Kandji / Jamf to match the Snipe-IT tag.
        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'tag-test-2',
            hostname: 'host-default-mapping',
            hardwareSerial: 'SN-2',
            hardwareModel: 'MacBook Pro',
            assetTag: 'VENDOR-TAG-002',
        );

        SyncHostFromAdapter::run($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-default-mapping',
            'asset_tag' => 'VENDOR-TAG-002',
        ]);
    }

    public function test_asset_tag_skip_target_preserves_curated_snipe_it_tag()
    {
        $fleet = $this->configuredFleetInstance();

        // Admins who want to protect curated tags flip the mapping
        // to skip. Vendor tag ignored on both create and update.
        SyncAdapterConfig::put($fleet->id, 'mapping.asset_tag', 'skip');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'tag-test-3',
            hostname: 'host-skipped-mapping',
            hardwareSerial: 'SN-3',
            hardwareModel: 'MacBook Pro',
            assetTag: 'VENDOR-TAG-003',
        );

        SyncHostFromAdapter::run($record);

        $created = \App\Models\Asset::where('name', 'host-skipped-mapping')->firstOrFail();
        $this->assertNotSame('VENDOR-TAG-003', $created->asset_tag);
    }

    public function test_adapter_declares_extra_fields_via_schema()
    {
        $fleet = $this->configuredFleetInstance();
        $adapter = new FleetAdapter($fleet);

        $extras = $adapter->extraFields();

        // Fleet's normalize() emits these four extras. the declaration
        // matches so the mapping UI knows to render dropdowns for
        // them.
        $this->assertArrayHasKey('fleet_team', $extras);
        $this->assertArrayHasKey('fleet_labels', $extras);
        $this->assertArrayHasKey('fleet_uuid', $extras);
        $this->assertArrayHasKey('fleet_status', $extras);
    }

    public function test_settings_page_renders_extra_field_dropdowns_for_adapter_that_declares_them()
    {
        // Side-effect factory create. The custom field is a fixture that
        // has to exist on the DB for the settings page to render it as an
        // extras-mapping target. The returned instance isn't referenced
        // by name; the assertion just checks the field's name string
        // shows up in the rendered HTML.
        CustomField::factory()->create(['element' => 'text', 'name' => 'Team Assignment']);

        $html = $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->get(route('settings.adapters.index', ['adapter' => 'fleet']))
            ->assertOk()
            ->getContent();

        // Rows for each Fleet extra field appear on the page.
        $this->assertStringContainsString('fleet_mapping[fleet_team]', $html);
        $this->assertStringContainsString('fleet_mapping[fleet_labels]', $html);
        // Custom field is offered as a target for extras.
        $this->assertStringContainsString('Team Assignment', $html);
    }

    private function configuredFleetInstance(): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/fleet');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return $instance->fresh();
    }

    /**
     * @param  array<int, string>|null  $labels
     * @return array<string, mixed>
     */
    private function fleetHost(
        int $id,
        string $hostname = 'host',
        string $hardware_model = 'Generic Laptop',
        ?string $team_name = null,
        ?array $labels = null,
    ): array {
        return [
            'id' => $id,
            'hostname' => $hostname,
            'hardware_model' => $hardware_model,
            'team_name' => $team_name,
            'labels' => $labels,
            'uuid' => 'fleet-uuid-'.$id,
            'status' => 'online',
        ];
    }
}
