<?php

use App\Models\User;
use Laravel\Passport\Passport;

test('disabled two factor lets any pat through', function () {
    // '' == disabled globally. Even an unenrolled superuser's PAT works,
    // matching the behavior of installs that haven't turned 2FA on.
    $this->settings->set(['two_factor_enabled' => '']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '0',
        'two_factor_optin' => '0',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))->assertOk();
});

test('optional two factor lets non optin users through', function () {
    // Mode 1: 2FA optional. A user who hasn't opted in must not be forced
    // into 403 territory by this middleware — that would break every
    // legacy PAT the moment an admin flips optional 2FA on.
    $this->settings->set(['two_factor_enabled' => '1']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '0',
        'two_factor_optin' => '0',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))->assertOk();
});

test('optional two factor blocks optin users who are not enrolled', function () {
    // Mode 1 + opted in but never finished enrollment = same policy state
    // as required mode: token owner has committed to 2FA but the second
    // factor isn't actually usable yet.
    $this->settings->set(['two_factor_enabled' => '1']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '0',
        'two_factor_optin' => '1',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))
        ->assertForbidden()
        ->assertJson(['status' => 'error']);
});

test('optional two factor lets optin enrolled users through', function () {
    $this->settings->set(['two_factor_enabled' => '1']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '1',
        'two_factor_optin' => '1',
        'two_factor_secret' => 'TESTSECRET',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))->assertOk();
});

test('required two factor blocks unenrolled pat', function () {
    $this->settings->set(['two_factor_enabled' => '2']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '0',
        'two_factor_optin' => '0',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))
        ->assertForbidden()
        ->assertJson(['status' => 'error']);
});

test('required two factor lets enrolled pat through', function () {
    $this->settings->set(['two_factor_enabled' => '2']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '1',
        'two_factor_optin' => '1',
        'two_factor_secret' => 'TESTSECRET',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))->assertOk();
});

test('required two factor blocks optin but unenrolled pat', function () {
    $this->settings->set(['two_factor_enabled' => '2']);
    $user = User::factory()->superuser()->create([
        'two_factor_enrolled' => '0',
        'two_factor_optin' => '1',
    ]);

    Passport::actingAs($user);

    $this->getJson(route('api.users.me'))->assertForbidden();
});

test('no token response is not our 403', function () {
    $this->settings->set(['two_factor_enabled' => '2']);

    $response = $this->getJson(route('api.users.me'));

    $this->assertNotEquals(403, $response->getStatusCode());
});
