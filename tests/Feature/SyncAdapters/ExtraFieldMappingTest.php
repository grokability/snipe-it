<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Fleet\FleetAdapter;
use App\SyncAdapters\MappingTargets;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

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
            SyncAdapter::syncFromRecord($record);
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
            SyncAdapter::syncFromRecord($record);
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
            SyncAdapter::syncFromRecord($record);
        }

        // Asset created, but the custom-field column stays null since
        // no mapping was stored.
        $this->assertDatabaseHas('assets', [
            'name' => 'wksn-03',
            $customField->db_column => null,
        ]);
    }

    public function test_text_extra_field_options_include_text_natives_but_not_date_or_boolean_targets()
    {
        CustomField::factory()->create(['element' => 'text', 'name' => 'Team Assignment']);
        CustomField::factory()->create(['element' => 'date_picker', 'name' => 'Warranty Expires']);
        CustomField::factory()->create(['element' => 'checkbox', 'name' => 'Flagged']);

        $options = MappingTargets::optionsForExtra('text');

        // No 'skip' target: the mapping-picker widget models
        // "unmapped" as the extra sitting in the picker's
        // available list, not as a committed row with target='skip'.
        $this->assertArrayNotHasKey('skip', $options);

        // Text-type extras route to text-shaped native asset columns
        // only. Date and boolean targets are excluded (#19953): an
        // ABM Product Family (text) must not be mappable to EOL Date.
        $this->assertArrayHasKey('native:asset_tag', $options);
        $this->assertArrayHasKey('native:model', $options);
        $this->assertArrayHasKey('native:model_number', $options);
        $this->assertArrayHasKey('native:notes', $options);
        $this->assertArrayHasKey('native:order_number', $options);
        $this->assertArrayNotHasKey('native:purchase_date', $options);
        $this->assertArrayNotHasKey('native:eol_date', $options);
        $this->assertArrayNotHasKey('native:byod', $options);

        // Date and checkbox custom fields must not appear in the text
        // pool either.
        foreach ($options as $target => $label) {
            if (str_starts_with($target, 'custom:')) {
                $this->assertStringNotContainsString('Warranty Expires', $label);
                $this->assertStringNotContainsString('Flagged', $label);
            }
        }
    }

    public function test_date_extra_field_options_include_date_natives_text_natives_and_both_text_and_date_custom_fields()
    {
        // Date extras route to date-shaped targets AND fall through
        // to text ones so admins can keep landing an ISO date string
        // in notes or a text custom field for display reasons. Only
        // non-date extras are blocked from date targets.
        CustomField::factory()->create(['element' => 'text', 'name' => 'Team Assignment']);
        CustomField::factory()->create(['element' => 'date_picker', 'name' => 'Warranty Expires']);
        CustomField::factory()->create(['element' => 'datetime_picker', 'name' => 'Last Audit']);
        CustomField::factory()->create(['element' => 'checkbox', 'name' => 'Flagged']);

        $options = MappingTargets::optionsForExtra('date');

        $this->assertArrayHasKey('native:purchase_date', $options);
        $this->assertArrayHasKey('native:eol_date', $options);
        $this->assertArrayHasKey('native:asset_tag', $options);
        $this->assertArrayHasKey('native:model', $options);
        $this->assertArrayHasKey('native:notes', $options);

        $customLabels = [];
        foreach ($options as $target => $label) {
            if (str_starts_with($target, 'custom:')) {
                $customLabels[] = $label;
            }
        }
        $this->assertStringContainsString('Team Assignment', implode(' ', $customLabels));
        $this->assertStringContainsString('Warranty Expires', implode(' ', $customLabels));
        $this->assertStringContainsString('Last Audit', implode(' ', $customLabels));
        foreach ($customLabels as $label) {
            $this->assertStringNotContainsString('Flagged', $label);
        }
    }

    public function test_boolean_extra_field_options_stay_custom_only()
    {
        CustomField::factory()->create(['element' => 'checkbox', 'name' => 'Flagged']);
        CustomField::factory()->create(['element' => 'text', 'name' => 'Team Assignment']);
        CustomField::factory()->create(['element' => 'date_picker', 'name' => 'Warranty Expires']);

        $options = MappingTargets::optionsForExtra('boolean');

        // Boolean-typed extras route to byod only on the native side,
        // and checkbox custom fields only on the custom side. Also no
        // 'skip' entry: unmap via ×, not via a stored 'skip' target.
        $this->assertArrayNotHasKey('skip', $options);
        $this->assertArrayHasKey('native:byod', $options);
        $this->assertArrayNotHasKey('native:asset_tag', $options);
        $this->assertArrayNotHasKey('native:model', $options);
        $this->assertArrayNotHasKey('native:notes', $options);
        $this->assertArrayNotHasKey('native:purchase_date', $options);
        $this->assertArrayNotHasKey('native:eol_date', $options);

        foreach ($options as $target => $label) {
            if (str_starts_with($target, 'custom:')) {
                $this->assertSame('Target: Flagged', $label);
            }
        }
    }

    public function test_extra_can_route_to_native_purchase_date()
    {
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:purchase_date');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'purchase-1',
            hostname: 'host-1',
            hardwareSerial: 'SN-1',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => '2024-03-15'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-1',
            'purchase_date' => '2024-03-15',
        ]);
    }

    public function test_purchase_date_accepts_iso_8601_datetime()
    {
        // ABM emits orderDateTime as ISO 8601 with a time component.
        // The write path normalizes to YYYY-MM-DD so it fits Snipe-IT's
        // date column.
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:purchase_date');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'purchase-2',
            hostname: 'host-2',
            hardwareSerial: 'SN-2',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => '2024-03-15T00:00:00Z'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-2',
            'purchase_date' => '2024-03-15',
        ]);
    }

    public function test_unparseable_purchase_date_is_skipped_not_crashed()
    {
        // Fail-safe: bad vendor data on one field should not abort the
        // whole sync. Asset still gets created, purchase_date stays
        // null, the sync run continues.
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:purchase_date');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'bad-date',
            hostname: 'host-3',
            hardwareSerial: 'SN-3',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => 'not-a-date'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', ['name' => 'host-3']);
        $this->assertDatabaseMissing('assets', ['name' => 'host-3', 'purchase_date' => 'not-a-date']);
    }

    public function test_extra_can_route_to_native_order_number()
    {
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:order_number');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'order-1',
            hostname: 'host-4',
            hardwareSerial: 'SN-4',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => 'PO-12345'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-4',
            'order_number' => 'PO-12345',
        ]);
    }

    public function test_extra_can_route_to_native_eol_date()
    {
        // Repro of issue #19953: AppleCare End Date (text-shaped extra
        // from the ABM adapter, delivered as YYYY-MM-DD after Carbon
        // normalization) now lands in Snipe-IT's asset_eol_date column
        // with eol_explicit set so the eolDate accessor returns the
        // vendor-sourced value.
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:eol_date');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'eol-1',
            hostname: 'host-eol',
            hardwareSerial: 'SN-EOL',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => '2027-09-30'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-eol',
            'asset_eol_date' => '2027-09-30',
            'eol_explicit' => 1,
        ]);
    }

    public function test_eol_date_accepts_iso_8601_datetime()
    {
        // ABM's AppleCare endDateTime arrives as ISO 8601 with a time
        // component. Mirrors the purchase_date normalization: we strip
        // to YYYY-MM-DD after an app-timezone rotation.
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:eol_date');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'eol-2',
            hostname: 'host-eol-iso',
            hardwareSerial: 'SN-EOL-ISO',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => '2027-09-30T00:00:00Z'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', [
            'name' => 'host-eol-iso',
            'asset_eol_date' => '2027-09-30',
        ]);
    }

    public function test_unparseable_eol_date_is_skipped_not_crashed()
    {
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:eol_date');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'eol-bad',
            hostname: 'host-eol-bad',
            hardwareSerial: 'SN-EOL-BAD',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => 'not-a-date'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('assets', ['name' => 'host-eol-bad']);
        $this->assertDatabaseMissing('assets', ['name' => 'host-eol-bad', 'asset_eol_date' => 'not-a-date']);
    }

    public function test_extra_can_route_to_native_model_number()
    {
        // Repro of issue #19953: ABM Part Number lands on the resolved
        // AssetModel row's model_number column. The normalized `model`
        // field writes first (resolveModelIdByName creates or matches
        // the AssetModel), then the extras pass updates its
        // model_number.
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:model_number');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'mn-1',
            hostname: 'host-mn',
            hardwareSerial: 'SN-MN',
            hardwareModel: 'MacBook Pro',
            extra: ['fleet_team' => 'MXTM3LL/A'],
        );

        SyncAdapter::syncFromRecord($record);

        $this->assertDatabaseHas('models', [
            'name' => 'MacBook Pro',
            'model_number' => 'MXTM3LL/A',
        ]);
    }

    public function test_model_number_write_reuses_existing_asset_model_when_value_matches()
    {
        // Second sync of the same device must not touch the AssetModel
        // row when the model_number already matches. Skipping the save
        // keeps every sync pass idempotent against the Watson
        // name+model_number uniqueness rule, which otherwise fires
        // against the row itself.
        $fleet = $this->configuredFleetInstance();
        SyncAdapterConfig::put($fleet->id, 'mapping.fleet_team', 'native:model_number');

        $record = new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'mn-idempotent',
            hostname: 'host-mn-idem',
            hardwareSerial: 'SN-MN-IDEM',
            hardwareModel: 'MacBook Air',
            extra: ['fleet_team' => 'MLY33LL/A'],
        );

        SyncAdapter::syncFromRecord($record);
        SyncAdapter::syncFromRecord($record);

        $this->assertSame(
            1,
            \App\Models\AssetModel::where('name', 'MacBook Air')
                ->where('model_number', 'MLY33LL/A')
                ->count(),
            'Second sync pass must not create a duplicate AssetModel row.',
        );
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

        SyncAdapter::syncFromRecord($record);

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

        SyncAdapter::syncFromRecord($record);

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

        SyncAdapter::syncFromRecord($record);

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

    public function test_settings_page_renders_extra_field_picker_for_adapter_that_declares_them()
    {
        // Side-effect factory create. The custom field is a fixture that
        // has to exist on the DB for the settings page to render it as
        // an extras-mapping target. The returned instance isn't
        // referenced by name. The assertion just checks the field's
        // name string shows up in the rendered HTML.
        CustomField::factory()->create(['element' => 'text', 'name' => 'Team Assignment']);

        $html = $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->get(route('settings.adapters.index', ['adapter' => 'fleet']))
            ->assertOk()
            ->getContent();

        // Each unmapped Fleet extra shows up as a picker option carrying
        // its key as the value and its resolved label as the option text
        // (mapping-picker widget replaced the one-row-per-extra @foreach).
        $this->assertStringContainsString('value="fleet_team"', $html);
        $this->assertStringContainsString('value="fleet_labels"', $html);
        // Custom field is offered as a target inside each option's
        // data-target-options JSON. The custom field's name shows up
        // literally in that JSON.
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
