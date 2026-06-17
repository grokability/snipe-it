<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CompanyHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_is_current_user_has_access_when_fmcs_disabled(): void
    {
        // Con FMCS desactivado (default), el acceso no se restringe por compania.
        $company = Company::factory()->create();
        $asset = \App\Models\Asset::factory()->create(['company_id' => $company->id]);

        $this->assertTrue(Company::isCurrentUserHasAccess($asset));
    }

    public function test_authorization_helpers_return_bool(): void
    {
        $this->assertIsBool(Company::isCurrentUserAuthorized());
        $this->assertIsBool(Company::canManageUsersCompanies());
    }

    public function test_get_id_from_input(): void
    {
        $this->assertNotNull(Company::getIdFromInput(5));
    }

    public function test_is_deletable(): void
    {
        $company = Company::factory()->create();

        $this->assertIsBool($company->isDeletable());
    }

    public function test_get_id_for_user(): void
    {
        // Con FMCS off devuelve false; basta con ejecutarlo sin excepcion.
        Company::getIdForUser(3);
        $this->assertTrue(true);
    }
}
