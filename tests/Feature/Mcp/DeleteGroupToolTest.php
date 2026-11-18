<?php

use App\Mcp\Tools\DeleteGroupTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function deleteGroupTool(array $args = []): ResponseFactory
{
    return (new DeleteGroupTool)->handle(new Request($args));
}

test('deletes group by id', function () {
    $group = Group::factory()->create();

    deleteGroupTool(['id' => $group->id]);

    $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
});

test('deletes group by name', function () {
    $group = Group::factory()->create();

    deleteGroupTool(['name' => $group->name]);

    $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
});

test('returns error when not found', function () {
    expect(deleteGroupTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $group = Group::factory()->create();

    expect(deleteGroupTool(['id' => $group->id])->responses()->first()->isError())->toBeTrue();
});
