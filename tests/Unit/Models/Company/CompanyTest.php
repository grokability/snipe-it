<?php

namespace Tests\Unit\Models\Company;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    public function test_a_company_can_have_users()
    {
        $company = Company::factory()->create();
        User::factory()->forCompany($company)->create();

        $this->assertCount(1, $company->users);
    }

    public function test_get_ids_for_current_user_returns_requested_ids_when_no_auth_user()
    {
        // Regression for #19949: CLI contexts (snipeit:import, scheduled
        // tasks) run without an auth user. The gate must not crash on
        // $current_user->isSuperUser() when auth()->user() is null.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $this->assertNull(auth()->user());
        $this->assertSame(
            [$companyA->id, $companyB->id],
            Company::getIdsForCurrentUser([$companyA->id, $companyB->id])
        );
    }

    public function test_get_ids_for_current_user_returns_requested_ids_for_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $this->actingAs(User::factory()->superuser()->create());

        $this->assertSame(
            [$companyA->id, $companyB->id],
            Company::getIdsForCurrentUser([$companyA->id, $companyB->id])
        );
    }

    public function test_get_ids_for_current_user_filters_to_pivot_memberships_for_non_superusers()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $user = User::factory()->create();
        $user->companies()->attach([$companyA->id, $companyB->id]);
        $this->actingAs($user);

        $this->assertEqualsCanonicalizing(
            [$companyA->id, $companyB->id],
            Company::getIdsForCurrentUser([$companyA->id, $companyB->id, $companyC->id])
        );
    }
}
