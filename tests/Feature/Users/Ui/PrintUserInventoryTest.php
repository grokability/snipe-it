<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
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
}
