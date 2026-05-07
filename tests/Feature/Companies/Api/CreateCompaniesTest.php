<?php

namespace Tests\Feature\Companies\Api;

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
}
