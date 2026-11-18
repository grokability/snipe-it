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

test('returns groups with pagination', function () {
    Group::factory()->count(2)->create();

    $content = handle()->getStructuredContent();

    expect($content)->toHaveKey('groups');
    expect($content)->toHaveKey('total');
    expect($content['total'])->toBeGreaterThanOrEqual(2);
});

test('each group has key fields', function () {
    Group::factory()->create();

    $content = handle(['limit' => 1])->getStructuredContent();

    expect($content['groups'])->not->toBeEmpty();
    $group = $content['groups'][0];
    expect($group)->toHaveKey('id');
    expect($group)->toHaveKey('name');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle()->responses()->first()->isError())->toBeTrue();
});
