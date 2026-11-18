<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewUsers()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('finds user by id', function () {
    $user = User::factory()->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['username'])->toEqual($user->username);
});

test('finds user by username', function () {
    $user = User::factory()->create(['username' => 'test.user.mcp']);

    $content = handle(['username' => 'test.user.mcp'])->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['username'])->toEqual('test.user.mcp');
});

test('finds user by email', function () {
    $user = User::factory()->create(['email' => 'mcp.test@example.com']);

    $content = handle(['email' => 'mcp.test@example.com'])->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['email'])->toEqual('mcp.test@example.com');
});

test('returns error when no identifier provided', function () {
    expect(handle([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not found by id', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not found by username', function () {
    expect(handle(['username' => 'no.such.user'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not found by email', function () {
    expect(handle(['email' => 'nobody@nowhere.invalid'])->responses()->first()->isError())->toBeTrue();
});

test('response includes expected fields', function () {
    $user = User::factory()->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    foreach (['id', 'username', 'email', 'first_name', 'last_name', 'activated', 'assets_count', 'licenses_count'] as $field) {
        expect($content)->toHaveKey($field);
    }
});

test('start and end dates render as y m d strings', function () {
    $user = User::factory()->create([
        'start_date' => '2025-01-15',
        'end_date' => '2026-06-30',
    ]);

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['start_date'])->toBe('2025-01-15');
    expect($content['end_date'])->toBe('2026-06-30');
});

test('start and end dates render as null when absent', function () {
    $user = User::factory()->create([
        'start_date' => null,
        'end_date' => null,
    ]);

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['start_date'])->toBeNull();
    expect($content['end_date'])->toBeNull();
});

test('response includes asset and license counts', function () {
    $user = User::factory()->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['assets_count'])->toBeInt();
    expect($content['licenses_count'])->toBeInt();
});

test('response includes company and department', function () {
    $user = User::factory()->create();

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content)->toHaveKey('company');
    expect($content)->toHaveKey('department');
    expect($content)->toHaveKey('location');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();

    expect(handle(['id' => $user->id])->responses()->first()->isError())->toBeTrue();
});
