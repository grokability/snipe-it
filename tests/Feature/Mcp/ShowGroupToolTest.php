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

test('returns group by id', function () {
    $group = Group::factory()->create();

    $content = handle(['id' => $group->id])->getStructuredContent();

    expect($content['id'])->toEqual($group->id);
    expect($content['name'])->toEqual($group->name);
});

test('returns group by name', function () {
    $group = Group::factory()->create();

    $content = handle(['name' => $group->name])->getStructuredContent();

    expect($content['id'])->toEqual($group->id);
    expect($content['name'])->toEqual($group->name);
});

test('returns error when no identifier provided', function () {
    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $group = Group::factory()->create();

    expect(handle(['id' => $group->id])->responses()->first()->isError())->toBeTrue();
});
