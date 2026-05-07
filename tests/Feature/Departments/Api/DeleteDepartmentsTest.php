<?php

namespace Tests\Feature\Departments\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteDepartmentsTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $department = Department::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.departments.destroy', $department))
            ->assertForbidden();

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $departmentA = Department::factory()->for($companyA)->create();
        $departmentB = Department::factory()->for($companyB)->create();
        $departmentC = Department::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteDepartments()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->deleteDepartments()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.departments.destroy', $departmentB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.departments.destroy', $departmentA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.departments.destroy', $departmentC))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('departments', ['id' => $departmentA->id]);
        $this->assertDatabaseHas('departments', ['id' => $departmentB->id]);
        $this->assertSoftDeleted($departmentC);
    }

    public function test_cannot_delete_department_that_still_has_users()
    {
        $department = Department::factory()->hasUsers()->create();

        $this->actingAsForApi(User::factory()->deleteDepartments()->create())
            ->deleteJson(route('api.departments.destroy', $department))
            ->assertStatusMessageIs('error');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_can_delete_department()
    {
        $department = Department::factory()->create();

        $this->actingAsForApi(User::factory()->deleteDepartments()->create())
            ->deleteJson(route('api.departments.destroy', $department))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($department);
    }
}
