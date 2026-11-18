<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('restores soft deleted user', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->assertSoftDeleted('users', ['id' => $user->id]);

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not deleted', function () {
    $user = User::factory()->create();

    expect(handle(['id' => $user->id])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create();
    $user->delete();

    expect(handle(['id' => $user->id])->responses()->first()->isError())->toBeTrue();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});
