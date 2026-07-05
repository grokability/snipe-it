<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * Pins User::canReceiveFromCompany() — the User-target branch of the FMCS
 * checkout rules in CompanyableTrait::canCheckoutTo(). Originally this method
 * returned true unconditionally for null-company items, which broke parity with
 * the asset/location branch: non-floater mode should restrict null-company
 * items to null-company users (flagged in PR review by @uberbrady on #19200).
 */
class UserCanReceiveFromCompanyTest extends TestCase
{
    public function test_uncompanied_item_to_uncompanied_user_is_always_allowed(): void
    {
        $this->settings->disableFloaterMode();
        $user = User::factory()->create();
        $user->companies()->sync([]);

        $this->assertTrue($user->canReceiveFromCompany(null));
    }

    public function test_uncompanied_item_to_companied_user_is_blocked_without_floater_mode(): void
    {
        $this->settings->disableFloaterMode();
        $company = Company::factory()->create();
        $user = $company->users()->save(User::factory()->create());

        $this->assertFalse($user->canReceiveFromCompany(null));
    }

    public function test_uncompanied_item_to_companied_user_is_allowed_with_floater_mode(): void
    {
        $this->settings->enableFloaterMode();
        $company = Company::factory()->create();
        $user = $company->users()->save(User::factory()->create());

        $this->assertTrue($user->canReceiveFromCompany(null));
    }

    public function test_companied_item_to_uncompanied_user_is_blocked_without_floater_mode(): void
    {
        $this->settings->disableFloaterMode();
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->sync([]);

        $this->assertFalse($user->canReceiveFromCompany($company->id));
    }

    public function test_companied_item_to_uncompanied_user_is_allowed_with_floater_mode(): void
    {
        $this->settings->enableFloaterMode();
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->sync([]);

        $this->assertTrue($user->canReceiveFromCompany($company->id));
    }

    public function test_companied_item_to_matching_companied_user_is_allowed(): void
    {
        $this->settings->disableFloaterMode();
        $company = Company::factory()->create();
        $user = $company->users()->save(User::factory()->create());

        $this->assertTrue($user->canReceiveFromCompany($company->id));
    }

    public function test_companied_item_to_non_matching_companied_user_is_blocked(): void
    {
        $this->settings->enableFloaterMode(); // even with floater mode on, a companied user is restricted to their own pivot
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = $companyA->users()->save(User::factory()->create());

        $this->assertFalse($user->canReceiveFromCompany($companyB->id));
    }
}
