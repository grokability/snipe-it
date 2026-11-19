<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\User;

test('requires permission', function () {
    $department = Department::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.departments.destroy', $department))
        ->assertForbidden();

    $this->assertDatabaseHas('departments', ['id' => $department->id]);
});

test('adheres to full multiple companies support scoping', function () {
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
});

test('cannot delete department that still has users', function () {
    $department = Department::factory()->hasUsers()->create();

    $this->actingAsForApi(User::factory()->deleteDepartments()->create())
        ->deleteJson(route('api.departments.destroy', $department))
        ->assertStatusMessageIs('error');

    $this->assertDatabaseHas('departments', ['id' => $department->id]);
});

test('can delete department', function () {
    $department = Department::factory()->create();

    $this->actingAsForApi(User::factory()->deleteDepartments()->create())
        ->deleteJson(route('api.departments.destroy', $department))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($department);
});
