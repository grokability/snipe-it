<?php

use App\Models\Department;
use App\Models\User;

test('requires permission to edit department', function () {
    $department = Department::factory()->create();
    $this->actingAsForApi(User::factory()->create())
        ->patchJson(route('api.departments.update', $department))
        ->assertForbidden();
});

test('can update department via patch', function () {
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
    expect($department->name)->toEqual('Test Department', 'Name was not updated');
    expect($department->notes)->toEqual('Test Note', 'Note was not updated');
});

test('array name is rejected and does not corrupt row', function () {
    // Regression: the API PATCH endpoint used the generic ImageUploadRequest
    // which does not validate `name`, so a caller could send name as an
    // array. The model's max:255 rule counted array items instead of string
    // length, save() proceeded, JSON-encoded the array as the DB column
    // value, and then the transformer TypeErrored inside htmlspecialchars.
    // Fixed by tightening the model rule to require a string.
    $department = Department::factory()->create(['name' => 'Original Name']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.departments.update', $department), [
            'name' => ['hax', 'hax2'],
        ])
        ->assertStatusMessageIs('error');

    $department->refresh();
    expect($department->name)->toBe('Original Name', 'Row was corrupted by an array payload — validation failed to reject it.');
});
