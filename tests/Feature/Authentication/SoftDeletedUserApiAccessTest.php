<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('trashed user is hidden from default eloquent query', function () {
    $user = User::factory()->create();
    $user->delete();

    expect(User::find($user->id))->toBeNull();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

test('eloquent user provider cannot retrieve trashed user', function () {
    $user = User::factory()->create();
    $id = $user->id;

    $provider = Auth::createUserProvider('users');
    expect($provider->retrieveById($id))->not->toBeNull();

    $user->delete();

    expect($provider->retrieveById($id))->toBeNull('EloquentUserProvider::retrieveById must return null for a soft-deleted user. '.
    'This is the invariant Passport TokenGuard relies on to refuse tokens for deleted accounts.');
});
