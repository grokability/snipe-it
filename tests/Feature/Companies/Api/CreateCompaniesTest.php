<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class CreateCompaniesTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.companies.store'))
            ->assertForbidden();
    }

    public function test_validation_for_creating_company()
    {
        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'))
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJsonStructure([
                'messages' => [
                    'name',
                ],
            ]);
    }

    public function test_can_create_company()
    {
        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'My Cool Company',
                'notes' => 'A Cool Note',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('companies', [
            'name' => 'My Cool Company',
            'notes' => 'A Cool Note',
        ]);
    }

    public function test_can_reuse_name_of_soft_deleted_company()
    {
        // Previously a DB-level unique index on companies.name made this
        // impossible — even if the original row was trashed, the schema
        // constraint matched it and the INSERT failed. The unique_undeleted
        // validation rule respects deleted_at, so the trashed row no
        // longer blocks creating a new one with the same name.
        $trashed = Company::factory()->create(['name' => 'Phoenix Co']);
        $trashed->delete();

        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), ['name' => 'Phoenix Co'])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('companies', [
            'name' => 'Phoenix Co',
            'deleted_at' => null,
        ]);
    }
}
