<?php

namespace Tests\Feature\Departments\Api;

use App\Models\Department;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateDepartmentsTest extends TestCase
{

    public function testRequiresPermissionToEditDepartment()
    {
        $department = Department::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.departments.update', $department))
            ->assertForbidden();
    }

    public function testCanUpdateDepartmentViaPatch()
    {
        $department = Department::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.departments.update', $department), [
                'name' => 'Test Department',
                'notes' => 'Test Note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $department->refresh();
        $this->assertEquals('Test Department', $department->name, 'Name was not updated');
        $this->assertEquals('Test Note', $department->notes, 'Note was not updated');

    }



}
