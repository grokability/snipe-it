<?php

namespace Tests\Feature\Companies\Api;

use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class CreateCompaniesTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.companies.store'))
            ->assertForbidden();
    }

    public function testValidationForCreatingCompany()
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

    public function testCanCreateCompany()
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
