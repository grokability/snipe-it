<?php

use App\Models\Department;
use App\Models\User;

test('clean department is bulk selectable', function () {
    $department = Department::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.departments.show', $department))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('department with users is not bulk selectable', function () {
    $department = Department::factory()->create();
    User::factory()->create(['department_id' => $department->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.departments.show', $department))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});
