<?php

namespace Tests\Unit\Models\Company;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class GetIdForCurrentUserTest extends TestCase
{
    public function testReturnsProvidedValueWhenFullCompanySupportDisabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->create());
        $this->assertEquals(1000, Company::getIdForCurrentUser(1000));
    }

    public function testReturnsProvidedValueForSuperUsersWhenFullCompanySupportEnabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->superuser()->create());
        $this->assertEquals(2000, Company::getIdForCurrentUser(2000));
    }

    public function testReturnsNonSuperUsersCompanyIdWhenFullCompanySupportEnabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->forCompany(['id' => 2000])->create());
        $this->assertEquals(2000, Company::getIdForCurrentUser(1000));
    }

    public function testReturnsNullForNonSuperUserWithoutCompanyIdWhenFullCompanySupportEnabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs(User::factory()->create(['company_id' => null]));
        $this->assertNull(Company::getIdForCurrentUser(1000));
    }
}
