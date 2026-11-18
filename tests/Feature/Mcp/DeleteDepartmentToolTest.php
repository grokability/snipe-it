<?php

use App\Mcp\Tools\DeleteDepartmentTool;
use App\Models\Department;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteDepartments()->create());
});

function deleteDepartmentTool(array $args): ResponseFactory
{
    return (new DeleteDepartmentTool)->handle(new Request($args));
}

test('deletes department by id', function () {
    $department = Department::factory()->create();

    $content = deleteDepartmentTool(['id' => $department->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('departments', ['id' => $department->id]);
});

test('deletes department by name', function () {
    $department = Department::factory()->create(['name' => 'Delete By Name Dept']);

    $content = deleteDepartmentTool(['name' => 'Delete By Name Dept'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('departments', ['id' => $department->id]);
});

test('response includes name', function () {
    $department = Department::factory()->create(['name' => 'Named Department']);

    $content = deleteDepartmentTool(['id' => $department->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named Department');
});

test('returns error when not found', function () {
    expect(deleteDepartmentTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when department has users', function () {
    $department = Department::factory()->create();
    User::factory()->create(['department_id' => $department->id]);

    $response = deleteDepartmentTool(['id' => $department->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('departments', ['id' => $department->id]);
});

test('returns error when user lacks permission', function () {
    $department = Department::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(deleteDepartmentTool(['id' => $department->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('departments', ['id' => $department->id]);
});
