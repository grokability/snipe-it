<?php

namespace Tests\Feature\Companies\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateCompaniesTest extends TestCase
{
    public function testRequiresPermissionToViewCreateCompanyPage()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('companies.create'))
            ->assertForbidden();
    }

    public function testCreateCompanyPageRenders()
    {
        $this->actingAs(User::factory()->createCompanies()->create())
            ->get(route('companies.create'))
            ->assertOk()
            ->assertViewIs('companies.edit');
    }

    public function testRequiresPermissionToCreateCompany()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('companies.store'))
            ->assertForbidden();
    }

    public function testValidDataRequiredToCreateCompany()
    {
        $this->actingAs(User::factory()->createCompanies()->create())
            ->post(route('companies.store'), [
                //
            ])
            ->assertSessionHasErrors([
                'name',
            ]);
    }

    public function testCanCreateCompany()
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
