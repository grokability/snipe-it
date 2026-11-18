<?php

use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteUsers()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes user by id', function () {
    $user = User::factory()->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('deletes user by username', function () {
    $user = User::factory()->create(['username' => 'delete.by.username']);

    $content = handle(['username' => 'delete.by.username'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('deletes user by email', function () {
    $user = User::factory()->create(['email' => 'delete.by@example.com']);

    $content = handle(['email' => 'delete.by@example.com'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('response includes username', function () {
    $user = User::factory()->create(['username' => 'response.check.mcp']);

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['username'])->toEqual('response.check.mcp');
});

test('returns error when user not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when deleting own account', function () {
    $authUser = auth()->user();

    $response = handle(['id' => $authUser->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('users', ['id' => $authUser->id]);
});

test('returns error when user has assigned assets', function () {
    $user = User::factory()->create();
    Asset::factory()->assignedToUser($user)->create();

    $response = handle(['id' => $user->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();

    expect(handle(['id' => $user->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});
