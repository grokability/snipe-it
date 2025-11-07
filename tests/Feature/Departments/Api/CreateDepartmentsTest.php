<?php

namespace Tests\Feature\Departments\Api;

use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Department;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateDepartmentsTest extends TestCase
{


    public function test_requires_permission_to_create_department()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.departments.store'))
            ->assertForbidden();
    }

    public function test_can_create_department_with_all_fields()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $manager = User::factory()->create();
        $user = User::factory()->superuser()->create();
        $response = $this->actingAsForApi($user)
            ->postJson(route('api.departments.store'), [
                'name'       => 'Test Department',
                'company_id' => $company->id,
                'location_id' => $location->id,
                'manager_id' => $manager->id,
                'notes'      => 'Test Note',
                'phone'      => '1234567890',
                'fax'        => '1234567890',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $this->assertTrue(Department::where('name', 'Test Department')->exists());

        $department = Department::find($response['payload']['id']);
        $this->assertEquals('Test Department', $department->name);
        $this->assertEquals('Test Note', $department->notes);

        $this->assertDatabaseHas('departments', [
            'name'        => 'Test Department',
            'company_id'  => $company->id,
            'location_id' => $location->id,
            'manager_id'  => $manager->id,
            'notes'       => 'Test Note',
            'phone'       => '1234567890',
            'fax'         => '1234567890',
            'created_by'  => $user->id,
        ]);
    }

    public function test_name_required_for_department()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.departments.store'), [
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_only_name_required_for_department()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.departments.store'), [
                'name' => 'Test Department',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');
    }

    public function test_arrays_not_allowed_for_numeric_fields()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.departments.store'), [
                'name'       => 'Test Department',
                'company_id' => [1, 2],
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_arrays_of_strings_are_not_allowed_for_numeric_fields()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.departments.store'), [
                'name'       => 'Test Department',
                'company_id' => ['1', '2'],
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }



}
