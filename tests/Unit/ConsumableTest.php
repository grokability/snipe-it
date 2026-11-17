<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class ConsumableTest extends TestCase
{
    public function test_percent_remaining_returns_one_hundred_when_nothing_is_checked_out()
    {
        $consumable = new Consumable([
            'qty' => 25,
        ]);
        $consumable->consumables_users_count = 0;

        $this->assertEquals(100, $consumable->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_value_when_partially_checked_out()
    {
        $consumable = new Consumable([
            'qty' => 20,
        ]);
        $consumable->consumables_users_count = 5;

        $this->assertEquals(75.0, $consumable->percentRemaining());
    }

    public function test_percent_remaining_can_go_negative_when_checked_out_exceeds_quantity()
    {
        $consumable = new Consumable([
            'qty' => 3,
        ]);
        $consumable->consumables_users_count = 5;

        $this->assertEqualsWithDelta(-66.66666666666667, $consumable->percentRemaining(), 0.0000000001);
    }

    public function test_num_checked_out_does_not_scope_by_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInCompanyB = User::factory()->forCompany($companyB)->create();

        $consumableForCompanyA = Consumable::factory()
            ->for($companyA)
            // this can happen if FMCS was toggled on after checkouts
            ->checkedOutToUser($userInCompanyB)
            ->checkedOutToUser($userInCompanyB)
            ->create(['qty' => 3]);

        $this->actingAs(User::factory()->superuser()->create());
        $this->assertEquals(2, $consumableForCompanyA->fresh()->numCheckedOut());

        $this->actingAs(User::factory()->admin()->forCompany($companyA)->create());
        $this->assertEquals(2, $consumableForCompanyA->fresh()->numCheckedOut());

        $this->actingAs(User::factory()->forCompany($companyA)->create());
        $this->assertEquals(2, $consumableForCompanyA->fresh()->numCheckedOut());
    }
}
