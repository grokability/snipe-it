<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class PrintUserInventoryTest extends TestCase
{
    public function test_permission_required_to_print_user_inventory()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.print', User::factory()->create()))
            ->assertStatus(403);
    }

    public function test_can_print_user_inventory()
    {
        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', User::factory()->create()))
            ->assertOk()
            ->assertStatus(200);
    }

    public function test_cannot_print_user_inventory_from_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->viewUsers()->create();
        $user = User::factory()->forCompany($companyB)->create();

        $this->actingAs($actor)
            ->get(route('users.print', $user))
            ->assertStatus(302);
    }

    public function test_bulk_print_user_inventory_does_not_error_on_missing_indirect_items_count()
    {
        $actor = User::factory()->viewUsers()->create();
        [$userA, $userB] = User::factory()->count(2)->create();

        $this->actingAs($actor)
            ->post(route('users/bulkedit'), [
                'ids' => [$userA->id, $userB->id],
                'bulk_actions' => 'print',
            ])
            ->assertOk();
    }

    public function test_user_without_licenses_view_cannot_see_assigned_licenses_in_print()
    {
        $subject = User::factory()->create();
        $license = License::factory()->create(['name' => 'Unique License XYZ123']);
        LicenseSeat::factory()->for($license)->assignedToUser($subject)->create();

        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertDontSee('Unique License XYZ123');
    }

    public function test_user_with_licenses_view_can_see_assigned_licenses_in_print()
    {
        $subject = User::factory()->create();
        $license = License::factory()->create(['name' => 'Unique License XYZ123']);
        LicenseSeat::factory()->for($license)->assignedToUser($subject)->create();

        $actor = User::factory()->viewUsers()->viewLicenses()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertSee('Unique License XYZ123');
    }

    public function test_user_without_accessories_view_cannot_see_assigned_accessories_in_print()
    {
        $subject = User::factory()->create();
        $accessory = Accessory::factory()->create(['name' => 'Unique Accessory ABC789']);
        $accessory->checkouts()->create(['assigned_to' => $subject->id, 'assigned_type' => User::class]);

        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertDontSee('Unique Accessory ABC789');
    }

    public function test_user_with_accessories_view_can_see_assigned_accessories_in_print()
    {
        $subject = User::factory()->create();
        $accessory = Accessory::factory()->create(['name' => 'Unique Accessory ABC789']);
        $accessory->checkouts()->create(['assigned_to' => $subject->id, 'assigned_type' => User::class]);

        $actor = User::factory()->viewUsers()->viewAccessories()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertSee('Unique Accessory ABC789');
    }

    public function test_user_without_consumables_view_cannot_see_assigned_consumables_in_print()
    {
        $subject = User::factory()->create();
        $consumable = Consumable::factory()->create(['name' => 'Unique Consumable DEF456']);
        $subject->consumables()->attach($consumable->id, ['created_by' => $subject->id]);

        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertDontSee('Unique Consumable DEF456');
    }

    public function test_user_with_consumables_view_can_see_assigned_consumables_in_print()
    {
        $subject = User::factory()->create();
        $consumable = Consumable::factory()->create(['name' => 'Unique Consumable DEF456']);
        $subject->consumables()->attach($consumable->id, ['created_by' => $subject->id]);

        $actor = User::factory()->viewUsers()->viewConsumables()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertSee('Unique Consumable DEF456');
    }

    public function test_user_without_assets_view_cannot_see_assigned_assets_in_print()
    {
        $subject = User::factory()->create();
        Asset::factory()->assignedToUser($subject)->create([
            'name' => 'Unique Asset LEAK111',
            'asset_tag' => 'LEAKTAG-111',
        ]);

        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertDontSee('Unique Asset LEAK111')
            ->assertDontSee('LEAKTAG-111');
    }

    public function test_user_with_assets_view_can_see_assigned_assets_in_print()
    {
        $subject = User::factory()->create();
        Asset::factory()->assignedToUser($subject)->create([
            'name' => 'Unique Asset LEAK111',
            'asset_tag' => 'LEAKTAG-111',
        ]);

        $actor = User::factory()->viewUsers()->viewAssets()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertSee('LEAKTAG-111');
    }

    public function test_user_without_components_view_cannot_see_asset_components_in_print()
    {
        $this->settings->set(['show_assigned_assets' => 1]);

        $subject = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($subject)->create();
        $component = Component::factory()->create(['name' => 'Unique Component COMP222']);
        $asset->components()->attach($component->id, [
            'assigned_qty' => 1,
            'created_by' => $subject->id,
            'created_at' => now(),
        ]);

        $actor = User::factory()->viewUsers()->viewAssets()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertDontSee('Unique Component COMP222');
    }

    public function test_user_with_components_view_can_see_asset_components_in_print()
    {
        $this->settings->set(['show_assigned_assets' => 1]);

        $subject = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($subject)->create();
        $component = Component::factory()->create(['name' => 'Unique Component COMP222']);
        $asset->components()->attach($component->id, [
            'assigned_qty' => 1,
            'created_by' => $subject->id,
            'created_at' => now(),
        ]);

        $actor = User::factory()->viewUsers()->viewAssets()->viewComponents()->create();

        $this->actingAs($actor)
            ->get(route('users.print', $subject))
            ->assertOk()
            ->assertSee('Unique Component COMP222');
    }

    public function test_bulk_print_without_assets_view_cannot_see_assigned_assets()
    {
        $subject = User::factory()->create();
        Asset::factory()->assignedToUser($subject)->create([
            'name' => 'Unique Asset BULK333',
            'asset_tag' => 'BULKTAG-333',
        ]);

        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->post(route('users/bulkedit'), [
                'ids' => [$subject->id],
                'bulk_actions' => 'print',
            ])
            ->assertOk()
            ->assertDontSee('BULKTAG-333');
    }

    public function test_bulk_print_with_assets_view_can_see_assigned_assets()
    {
        $subject = User::factory()->create();
        Asset::factory()->assignedToUser($subject)->create([
            'name' => 'Unique Asset BULK333',
            'asset_tag' => 'BULKTAG-333',
        ]);

        $actor = User::factory()->viewUsers()->viewAssets()->create();

        $this->actingAs($actor)
            ->post(route('users/bulkedit'), [
                'ids' => [$subject->id],
                'bulk_actions' => 'print',
            ])
            ->assertOk()
            ->assertSee('BULKTAG-333');
    }

    public function test_bulk_print_without_licenses_view_cannot_see_assigned_licenses()
    {
        $subject = User::factory()->create();
        $license = License::factory()->create(['name' => 'Unique License BULK444']);
        LicenseSeat::factory()->for($license)->assignedToUser($subject)->create();

        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->post(route('users/bulkedit'), [
                'ids' => [$subject->id],
                'bulk_actions' => 'print',
            ])
            ->assertOk()
            ->assertDontSee('Unique License BULK444');
    }
}
