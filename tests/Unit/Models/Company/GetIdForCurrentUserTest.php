<?php

namespace Tests\Unit\Models\Company;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class GetIdForCurrentUserTest extends TestCase
{
    public function test_returns_provided_value_when_full_company_support_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->create());
        $this->assertEquals(1000, Company::getIdForCurrentUser(1000));
    }

    public function test_returns_provided_value_for_super_users_when_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->superuser()->create());
        $this->assertEquals(2000, Company::getIdForCurrentUser(2000));
    }

    public function test_returns_non_super_users_company_id_when_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->forCompany(['id' => 2000])->create());
        $this->assertEquals(2000, Company::getIdForCurrentUser(1000));
    }

    public function test_returns_null_for_non_super_user_without_company_id_when_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->create(['company_id' => null]));
        $this->assertNull(Company::getIdForCurrentUser(1000));
    }
}
