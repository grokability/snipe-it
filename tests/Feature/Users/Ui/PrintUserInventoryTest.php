<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Accessory;
use App\Models\Company;
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

        $actor = User::factory()->for($companyA)->viewUsers()->create();
        $user = User::factory()->for($companyB)->create();

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
}
