<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class UpdateCompaniesTest extends TestCase
{
    public function testRequiresPermissionToPatchCompany()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.companies.update', $company))
            ->assertForbidden();
    }

    public function testValidationForPatchingCompany()
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

    public function testCanPatchCompany()
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
}
