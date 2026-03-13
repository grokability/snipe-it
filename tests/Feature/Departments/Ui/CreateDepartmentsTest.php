<?php

namespace Tests\Feature\Departments\Ui;

use App\Models\Component;
use App\Models\Department;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CreateDepartmentsTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('departments.create'))
            ->assertOk();
    }

    public function testPermissionRequiredToCreateDepartment()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('departments.store'), [
                'name' => 'Test Department',
                'company_id' => Company::factory()->create()->id
            ])
            ->assertForbidden();
    }

    public function testUserCanCreateDepartments()
    {
        $this->assertFalse(Department::where('name', 'Test Department')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('departments.store'), [
                'name' => 'Test Department',
                'company_id' => Company::factory()->create()->id,
                'notes' => 'Test Note',
            ])
            ->assertRedirect(route('departments.index'));

        $this->assertTrue(Department::where('name', 'Test Department')->where('notes', 'Test Note')->exists());
    }


}
