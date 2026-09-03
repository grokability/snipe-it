<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Coverage for per-adapter vendor-group to Snipe-IT-company mapping.
 * When an adapter supports group scoping and the sync record carries
 * a vendorGroupId that has a mapping, the resulting asset lands in
 * the mapped company. Otherwise it falls back to the instance's own
 * company_id. Uses Fleet as the exemplar because Fleet Teams are a
 * clean semantic match for company scoping.
 */
class GroupCompanyMappingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_mapped_group_lands_asset_in_mapped_company()
    {
        $customerA = Company::factory()->create(['name' => 'Customer A']);
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'group_mapping.42', (string) $customerA->id);

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'gc-1',
            hostname: 'customer-a-laptop',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            vendorGroupId: '42',
        ));

        $asset = Asset::where('name', 'customer-a-laptop')->firstOrFail();
        $this->assertSame($customerA->id, (int) $asset->company_id);
    }

    public function test_unmapped_group_falls_back_to_instance_company()
    {
        $customerA = Company::factory()->create();
        $fleet = $this->configuredFleet($customerA->id);

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'gc-2',
            hostname: 'unmapped-group-laptop',
            hardwareSerial: 'SN-B',
            hardwareModel: 'MacBook Pro',
            vendorGroupId: '999',
        ));

        $asset = Asset::where('name', 'unmapped-group-laptop')->firstOrFail();
        $this->assertSame($customerA->id, (int) $asset->company_id);
    }

    public function test_missing_vendor_group_id_uses_instance_company()
    {
        $customerA = Company::factory()->create();
        $fleet = $this->configuredFleet($customerA->id);
        SyncAdapterConfig::put($fleet->id, 'group_mapping.42', '999');

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'gc-3',
            hostname: 'no-group-laptop',
            hardwareSerial: 'SN-C',
            hardwareModel: 'MacBook Pro',
            vendorGroupId: null,
        ));

        $asset = Asset::where('name', 'no-group-laptop')->firstOrFail();
        $this->assertSame($customerA->id, (int) $asset->company_id);
    }

    public function test_group_change_on_subsequent_sync_reassigns_company()
    {
        $customerA = Company::factory()->create();
        $customerB = Company::factory()->create();
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'group_mapping.10', (string) $customerA->id);
        SyncAdapterConfig::put($fleet->id, 'group_mapping.20', (string) $customerB->id);

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'gc-4',
            hostname: 'moving-laptop',
            hardwareSerial: 'SN-D',
            hardwareModel: 'MacBook Pro',
            vendorGroupId: '10',
        ));

        $asset = Asset::where('name', 'moving-laptop')->firstOrFail();
        $this->assertSame($customerA->id, (int) $asset->company_id);

        SyncHostFromAdapter::run(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'gc-4',
            hostname: 'moving-laptop',
            hardwareSerial: 'SN-D',
            hardwareModel: 'MacBook Pro',
            vendorGroupId: '20',
        ));

        $asset->refresh();
        $this->assertSame($customerB->id, (int) $asset->company_id);
    }

    public function test_group_mappings_accessor_returns_id_to_id_map()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'group_mapping.10', '5');
        SyncAdapterConfig::put($fleet->id, 'group_mapping.20', '7');

        $adapter = $fleet->fresh()->adapter();
        $this->assertSame(['10' => 5, '20' => 7], $adapter->groupMappings());
    }

    public function test_refresh_groups_endpoint_fetches_and_caches_vendor_groups()
    {
        $fleet = $this->configuredFleet();

        \Illuminate\Support\Facades\Http::fake([
            '*/api/latest/fleet/teams' => \Illuminate\Support\Facades\Http::response([
                'teams' => [
                    ['id' => 5, 'name' => 'Engineering'],
                    ['id' => 7, 'name' => 'Sales'],
                ],
            ]),
        ]);

        $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->post(route('settings.adapters.refresh_groups', $fleet->slug))
            ->assertRedirect()
            ->assertSessionHas('success');

        $stored = json_decode(SyncAdapterConfig::get($fleet->id, 'cached_groups'), true);
        $this->assertSame(['id' => '5', 'label' => 'Engineering'], $stored[0]);
        $this->assertSame(['id' => '7', 'label' => 'Sales'], $stored[1]);
    }

    public function test_refresh_groups_endpoint_404s_for_adapter_without_group_scoping()
    {
        // Zentral is a stub adapter that doesn't opt into group scoping,
        // so its refresh-groups endpoint is not reachable.
        $zentral = SyncAdapterInstance::where('slug', 'zentral')->firstOrFail();
        SyncAdapterConfig::put($zentral->id, 'url', 'https://zentral.example.test');
        SyncAdapterConfig::put($zentral->id, 'token', Crypt::encrypt('t'));
        $zentral->active = true;
        $zentral->save();

        $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->post(route('settings.adapters.refresh_groups', 'zentral'))
            ->assertNotFound();
    }

    public function test_saving_adapter_persists_group_mappings_from_form()
    {
        $customerA = Company::factory()->create();
        $fleet = $this->configuredFleet();

        $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), [
                'fleet_url' => 'https://example.com/fleet',
                'fleet_token' => 'fake-token',
                'fleet_group_mapping' => ['42' => (string) $customerA->id],
                'fleet_default_category_id' => Category::factory()->create()->id,
                'fleet_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect();

        $this->assertSame(
            (string) $customerA->id,
            SyncAdapterConfig::get($fleet->id, 'group_mapping.42'),
        );
    }

    public function test_blank_mapping_clears_an_existing_group_mapping()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'group_mapping.42', '99');

        $this->actingAs(\App\Models\User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), [
                'fleet_url' => 'https://example.com/fleet',
                'fleet_token' => 'fake-token',
                'fleet_group_mapping' => ['42' => ''],
                'fleet_default_category_id' => Category::factory()->create()->id,
                'fleet_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect();

        $this->assertNull(SyncAdapterConfig::get($fleet->id, 'group_mapping.42'));
    }

    private function configuredFleet(?int $companyId = null): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        $instance->company_id = $companyId;
        $instance->active = true;
        $instance->save();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/fleet');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return $instance->fresh();
    }
}
