<?php

use App\Mcp\Tools\CreateDepartmentTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createDepartments()->create());
});

function createDepartmentTool(array $args): ResponseFactory
{
    return (new CreateDepartmentTool)->handle(new Request($args));
}

test('creates department with required fields', function () {
    $content = createDepartmentTool(['name' => 'Test Department'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('departments', ['name' => 'Test Department']);
});

test('response includes id and name', function () {
    $content = createDepartmentTool(['name' => 'Response Department'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Department');
});

test('creates department with optional fields', function () {
    $location = Location::factory()->create();
    $manager = User::factory()->create();

    createDepartmentTool([
        'name' => 'Full Department',
        'location_id' => $location->id,
        'manager_id' => $manager->id,
        'phone' => '555-1234',
        'fax' => '555-5678',
        'notes' => 'Department notes',
    ]);

    $this->assertDatabaseHas('departments', [
        'name' => 'Full Department',
        'location_id' => $location->id,
        'manager_id' => $manager->id,
        'phone' => '555-1234',
        'fax' => '555-5678',
    ]);
});

test('returns error when name missing', function () {
    expect(createDepartmentTool([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when location does not exist', function () {
    expect(createDepartmentTool([
        'name' => 'Bad Location Dept',
        'location_id' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createDepartmentTool(['name' => 'Blocked Department'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('departments', ['name' => 'Blocked Department']);
});
