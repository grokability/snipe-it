<?php

use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes group by id', function () {
    $group = Group::factory()->create();

    handle(['id' => $group->id]);

    $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
});

test('deletes group by name', function () {
    $group = Group::factory()->create();

    handle(['name' => $group->name]);

    $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $group = Group::factory()->create();

    expect(handle(['id' => $group->id])->responses()->first()->isError())->toBeTrue();
});
