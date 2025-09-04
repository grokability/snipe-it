<?php

namespace Tests\Feature\Departments\Ui;

use App\Models\Department;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateDepartmentsTest extends TestCase
{
    public function testPermissionRequiredToStoreDepartment()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('departments.store'), [
                'name' => 'Test Department',
            ])
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('departments.edit', Department::factory()->create()))
            ->assertOk();
    }

    public function testUserCanEditDepartments()
    {
        $department = Department::factory()->create(['name' => 'Test Department']);
        $this->assertTrue(Department::where('name', 'Test Department')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('departments.update', $department), [
                'name' => 'Test Department Edited',
                'notes' => 'Test Note Edited',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('departments.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertTrue(Department::where('name', 'Test Department Edited')->where('notes', 'Test Note Edited')->exists());

    }



}
