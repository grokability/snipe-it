<?php

use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewUsers()->viewAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('returns assets for user', function () {
    $user = User::factory()->create();
    Asset::factory()->assignedToUser($user)->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['user_id'])->toEqual($user->id);
    expect($content['total'])->toEqual(1);
    expect($content['assets'])->toHaveCount(1);
});

test('returns empty array when user has no assets', function () {
    $user = User::factory()->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['total'])->toEqual(0);
    expect($content['assets'])->toHaveCount(0);
});

test('returns error when user not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create();

    expect(handle(['id' => $user->id])->responses()->first()->isError())->toBeTrue();
});
