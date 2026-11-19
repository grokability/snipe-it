<?php

use App\Models\Setting;
use App\Models\User;

/**
 * Regression coverage for the REMOTE_USER-header spoofing vulnerability
 * reported by Brayden Arnold. `LoginController::loginViaRemoteUser` read
 * `$request->server($header_name)` where `$header_name` was any string
 * an admin configured. PHP populates `$_SERVER[HTTP_*]` directly from
 * inbound HTTP headers, so configuring an HTTP_-prefixed value let any
 * unauthenticated client send the matching request header and get
 * logged in as any active user, including superuser (pre-auth
 * takeover).
 *
 * Fix is two layers:
 *
 *   1. Settings-side validation rejects HTTP_-prefixed values at save
 *      time (StoreSecuritySettings::rules), so admins can't configure
 *      the dangerous shape on new installs.
 *   2. Runtime guard in `loginViaRemoteUser` refuses to honor an
 *      HTTP_-prefixed header name even when one is already persisted
 *      (covers upgrade paths from a pre-fix install).
 */
test('settings save rejects http prefixed header name', function () {
    $superuser = User::factory()->superuser()->create();

    $this->actingAs($superuser)
        ->post(route('settings.security.save'), [
            'pwd_secure_min' => 10,
            'login_remote_user_enabled' => 1,
            'login_remote_user_header_name' => 'HTTP_X_AUTH_USER',
        ])
        ->assertSessionHasErrors('login_remote_user_header_name');

    $this->assertNotSame(
        'HTTP_X_AUTH_USER',
        Setting::getSettings()->fresh()->login_remote_user_header_name,
        'HTTP_-prefixed header name must not persist.',
    );
});

test('settings save rejects lowercase http prefix too', function () {
    // Case-insensitive match catches "http_" too. PHP wouldn't
    // actually populate $_SERVER['http_x_auth_user'] from an
    // inbound header (keys are canonical uppercase), but blocking
    // it anyway heads off confused admins who think a lowercase
    // variant is somehow safer.
    $superuser = User::factory()->superuser()->create();

    $this->actingAs($superuser)
        ->post(route('settings.security.save'), [
            'pwd_secure_min' => 10,
            'login_remote_user_enabled' => 1,
            'login_remote_user_header_name' => 'http_x_auth_user',
        ])
        ->assertSessionHasErrors('login_remote_user_header_name');
});

test('settings save allows remote user default', function () {
    $superuser = User::factory()->superuser()->create();

    $this->actingAs($superuser)
        ->post(route('settings.security.save'), [
            'pwd_secure_min' => 10,
            'login_remote_user_enabled' => 1,
            'login_remote_user_header_name' => 'REMOTE_USER',
        ])
        ->assertSessionDoesntHaveErrors('login_remote_user_header_name');
});

test('runtime refuses http prefixed header even when already persisted', function () {
    // Simulate an upgrade path from a pre-fix install where the
    // dangerous value was saved before the validation rule landed.
    // Settings persisted via query builder so the new validation
    // can't block it. The runtime guard inside loginViaRemoteUser
    // must still refuse to honor the value.
    $victim = User::factory()->superuser()->create(['username' => 'super']);

    \DB::table('settings')->update([
        'login_remote_user_enabled' => 1,
        'login_remote_user_header_name' => 'HTTP_X_AUTH_USER',
    ]);
    Setting::$_cache = null;

    $this->withServerVariables(['HTTP_X_AUTH_USER' => 'super'])
        ->get('/login')
        ->assertOk();

    $this->assertGuest(
        null,
        'HTTP_-prefixed header name must not auto-login the user even when persisted pre-fix.',
    );
    $this->assertNotSame($victim->id, auth()->id(), 'No session should have been created from the spoofed header.');
});

test('legitimate remote user login still works', function () {
    // Baseline: the fix must not break REMOTE_USER-based auth for
    // installs using Apache mod_auth_* or similar upstreams that
    // set REMOTE_USER server-side. The server variable is set
    // directly (not from an inbound header) via withServerVariables,
    // which mirrors what a legitimate upstream would do.
    $user = User::factory()->create(['username' => 'alice']);

    \DB::table('settings')->update([
        'login_remote_user_enabled' => 1,
        'login_remote_user_header_name' => 'REMOTE_USER',
    ]);
    Setting::$_cache = null;

    $this->withServerVariables(['REMOTE_USER' => 'alice'])
        ->get('/login')
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});
