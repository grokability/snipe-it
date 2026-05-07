<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class UpdateCompaniesTest extends TestCase
{
    public function test_requires_permission_to_patch_company()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.companies.update', $company))
            ->assertForbidden();
    }

    public function test_validation_for_patching_company()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $company->id]), [
                'name' => '',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJsonStructure([
                'messages' => [
                    'name',
                ],
            ]);
    }

    public function test_can_patch_company()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $company->id]), [
                'name' => 'A Changed Name',
                'notes' => 'A Changed Note',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $company->refresh();
        $this->assertEquals('A Changed Name', $company->name);
        $this->assertEquals('A Changed Note', $company->notes);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {

        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->editCompanies()->create());
        $userInCompanyB = $companyB->users()->save(User::factory()->editCompanies()->create());

        $this->actingAsForApi($userInCompanyA)
            ->patchJson(route('api.companies.update', ['company' => $companyA->id]), [
                'name' => 'A Changed Name',
                'notes' => 'A Changed Note',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->actingAsForApi($userInCompanyB)
            ->patchJson(route('api.companies.update', ['company' => $companyB]), [
                'name' => 'Another Changed Name',
                'notes' => 'Another Changed Note',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->actingAsForApi($userInCompanyA)
            ->patchJson(route('api.companies.update', ['company' => $companyB->id]), [
                'name' => 'Yet Another Changed Name',
                'notes' => 'Yet Another Changed Note',
            ])
            ->assertJson([
                'messages' => 'Company not found',
            ])
            ->assertStatusMessageIs('error')
            ->assertStatus(200);

        $this->actingAsForApi($superUser)
            ->patchJson(route('api.companies.update', ['company' => $companyB->id]), [
                'name' => 'One Final Changed Name',
                'notes' => 'One Final Changed Note',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

    }
}
