<?php

namespace Tests\Feature\Companies\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateCompaniesTest extends TestCase
{
    public function test_requires_permission_to_view_create_company_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('companies.create'))
            ->assertForbidden();
    }

    public function test_create_company_page_renders()
    {
        $this->actingAs(User::factory()->createCompanies()->create())
            ->get(route('companies.create'))
            ->assertOk()
            ->assertViewIs('companies.edit');
    }

    public function test_requires_permission_to_create_company()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('companies.store'))
            ->assertForbidden();
    }

    public function test_valid_data_required_to_create_company()
    {
        $this->actingAs(User::factory()->createCompanies()->create())
            ->post(route('companies.store'), [
                //
            ])
            ->assertSessionHasErrors([
                'name',
            ]);
    }

    public function test_can_create_company()
    {
        $data = [
            'email' => 'email@example.com',
            'fax' => '619-666-6666',
            'name' => 'My New Company',
            'phone' => '619-555-5555',
        ];

        $user = User::factory()->createCompanies()->create();

        $this->actingAs($user)
            ->post(route('companies.store'), array_merge($data, ['redirect_option' => 'index']))
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseHas('companies', array_merge($data));
    }
}
