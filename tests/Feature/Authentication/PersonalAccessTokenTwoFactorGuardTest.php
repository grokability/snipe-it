<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

function seedPassportPersonalAccessClient(): void
{
    // Passport's personal-access-token flow requires a personal access
    // client to exist. The migration that ships with this app doesn't
    // seed one for tests, so create + register it before each test that
    // calls Passport::createToken (the happy-path cases).
    $clientId = (string) \Illuminate\Support\Str::uuid();
    DB::table('oauth_clients')->insert([
        'id' => $clientId,
        'user_id' => null,
        'name' => 'Test Personal Client',
        'secret' => 'secret',
        'redirect' => 'http://localhost/callback',
        'personal_access_client' => 1,
        'password_client' => 0,
        'revoked' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('oauth_personal_access_clients')->insert([
        'client_id' => $clientId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('cannot create personal access token before completing two factor', function () {
    $this->settings->set(['two_factor_enabled' => '2']); // required for everyone
    $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create([
        'two_factor_optin' => '1',
        'two_factor_enrolled' => '1',
        'two_factor_secret' => 'TESTSECRET',
    ]);

    // actingAs sets auth() but does NOT mark the session as 2FA-cleared.
    $this->actingAs($user, 'api')
        ->postJson(route('api.personal-access-token.create'), ['name' => 'poc'])
        ->assertForbidden();

    $this->assertDatabaseMissing('oauth_access_tokens', ['name' => 'poc']);
});

test('cannot list personal access tokens before completing two factor', function () {
    $this->settings->set(['two_factor_enabled' => '2']);
    $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create([
        'two_factor_optin' => '1',
        'two_factor_enrolled' => '1',
        'two_factor_secret' => 'TESTSECRET',
    ]);

    $this->actingAs($user, 'api')
        ->getJson(route('api.personal-access-token.index'))
        ->assertForbidden();
});

test('can create personal access token after completing two factor', function () {
    seedPassportPersonalAccessClient();
    $this->settings->set(['two_factor_enabled' => '2']);
    $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create([
        'two_factor_optin' => '1',
        'two_factor_enrolled' => '1',
        'two_factor_secret' => 'TESTSECRET',
    ]);

    $this->actingAs($user, 'api')
        // simulate the post-code state the TwoFactorAuthController sets
        ->withSession(['2fa_authed' => $user->id])
        ->postJson(route('api.personal-access-token.create'), ['name' => 'valid'])
        ->assertOk();
});

test('can create personal access token when two factor is disabled', function () {
    // Setting "two_factor_enabled" empty == disabled globally; the guard
    // returns true so existing installs without 2FA are unaffected.
    seedPassportPersonalAccessClient();
    $this->settings->set(['two_factor_enabled' => '']);
    $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create();

    $this->actingAs($user, 'api')
        ->postJson(route('api.personal-access-token.create'), ['name' => 'no2fa'])
        ->assertOk();
});

test('optional two factor user not opted in can still create token', function () {
    // 2FA optional, user hasn't opted in — they shouldn't be blocked.
    seedPassportPersonalAccessClient();
    $this->settings->set(['two_factor_enabled' => '1']);
    $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create(['two_factor_optin' => '0']);

    $this->actingAs($user, 'api')
        ->postJson(route('api.personal-access-token.create'), ['name' => 'optout'])
        ->assertOk();
});
