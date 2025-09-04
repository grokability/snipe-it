<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteCompaniesTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.companies.destroy', $company))
            ->assertForbidden();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function testCannotDeleteCompanyThatHasAssociatedItems()
    {
        $companyWithAssets = Company::factory()->hasAssets()->create();
        $companyWithAccessories = Company::factory()->hasAccessories()->create();
        $companyWithConsumables = Company::factory()->hasConsumables()->create();
        $companyWithComponents = Company::factory()->hasComponents()->create();
        $companyWithUsers = Company::factory()->hasUsers()->create();

        $actor = $this->actingAsForApi(User::factory()->deleteCompanies()->create());

        $actor->deleteJson(route('api.companies.destroy', $companyWithAssets))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.companies.destroy', $companyWithAccessories))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.companies.destroy', $companyWithConsumables))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.companies.destroy', $companyWithComponents))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.companies.destroy', $companyWithUsers))->assertStatusMessageIs('error');

        $this->assertDatabaseHas('companies', ['id' => $companyWithAssets->id]);
        $this->assertDatabaseHas('companies', ['id' => $companyWithAccessories->id]);
        $this->assertDatabaseHas('companies', ['id' => $companyWithConsumables->id]);
        $this->assertDatabaseHas('companies', ['id' => $companyWithComponents->id]);
        $this->assertDatabaseHas('companies', ['id' => $companyWithUsers->id]);
    }

    public function testCanDeleteCompany()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->deleteCompanies()->create())
            ->deleteJson(route('api.companies.destroy', $company))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {

        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteCompanies()->create());

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.companies.destroy', $companyB))
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.companies.destroy', $companyB))
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

    }
}
