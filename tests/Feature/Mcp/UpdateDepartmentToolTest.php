<?php

use App\Mcp\Tools\UpdateDepartmentTool;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editDepartments()->create());
});

function updateDepartmentTool(array $args): ResponseFactory
{
    return (new UpdateDepartmentTool)->handle(new Request($args));
}

test('updates department by id', function () {
    $department = Department::factory()->create(['name' => 'Original Name']);

    $content = updateDepartmentTool([
        'id' => $department->id,
        'new_name' => 'Updated Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Updated Name']);
});

test('updates department by name', function () {
    $department = Department::factory()->create(['name' => 'Find By Name Dept']);

    $content = updateDepartmentTool([
        'name' => 'Find By Name Dept',
        'new_name' => 'Renamed Dept',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Renamed Dept']);
});

test('response includes id and name', function () {
    $department = Department::factory()->create();

    $content = updateDepartmentTool([
        'id' => $department->id,
        'new_name' => 'Response Check Dept',
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Check Dept');
});

test('updates multiple fields', function () {
    $department = Department::factory()->create();
    $location = Location::factory()->create();
    $manager = User::factory()->create();

    updateDepartmentTool([
        'id' => $department->id,
        'location_id' => $location->id,
        'manager_id' => $manager->id,
        'phone' => '555-9999',
        'fax' => '555-8888',
        'notes' => 'Updated notes',
    ]);

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'location_id' => $location->id,
        'manager_id' => $manager->id,
        'phone' => '555-9999',
    ]);
});

test('returns error when not found', function () {
    expect(updateDepartmentTool(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when no identifier provided', function () {
    expect(updateDepartmentTool(['new_name' => 'No ID'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $department = Department::factory()->create(['name' => 'Unchanged Dept']);
    $this->actingAs(User::factory()->create());

    expect(updateDepartmentTool([
        'id' => $department->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Unchanged Dept']);
});
