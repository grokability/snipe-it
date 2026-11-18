<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editUsers()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('resets two factor for user', function () {
    $user = User::factory()->create([
        'two_factor_enrolled' => 1,
        'two_factor_optin' => 1,
        'two_factor_secret' => 'somesecret',
    ]);

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();

    $user->refresh();
    expect($user->two_factor_enrolled)->toEqual(0);
    expect($user->two_factor_optin)->toEqual(0);
    expect($user->two_factor_secret)->toBeNull();
});

test('returns error when user not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create(['two_factor_enrolled' => 1]);

    expect(handle(['id' => $user->id])->responses()->first()->isError())->toBeTrue();

    $user->refresh();
    expect($user->two_factor_enrolled)->toEqual(1);
});
