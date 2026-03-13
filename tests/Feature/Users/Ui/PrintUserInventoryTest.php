<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class PrintUserInventoryTest extends TestCase
{
    public function testPermissionRequiredToPrintUserInventory()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.print', User::factory()->create()))
            ->assertStatus(403);
    }

    public function testCanPrintUserInventory()
    {
        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', User::factory()->create()))
            ->assertOk()
            ->assertStatus(200);
    }

    public function testCannotPrintUserInventoryFromAnotherCompany()
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
