<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;

function seedOAuthClient(): string
{
    $clientId = (string) \Illuminate\Support\Str::uuid();
    DB::table('oauth_clients')->insert([
        'id' => $clientId,
        'user_id' => null,
        'name' => 'Test OAuth Client',
        'secret' => 'secret',
        'redirect' => 'http://localhost/callback',
        'personal_access_client' => 0,
        'password_client' => 1,
        'revoked' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $clientId;
}

function seedAccessToken(string $id, User $user, string $clientId): void
{
    DB::table('oauth_access_tokens')->insert([
        'id' => $id,
        'user_id' => $user->id,
        'client_id' => $clientId,
        'name' => $id,
        'scopes' => '[]',
        'revoked' => 0,
        'created_at' => now(),
        'updated_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
}

function seedRefreshToken(string $id, string $accessTokenId): void
{
    DB::table('oauth_refresh_tokens')->insert([
        'id' => $id,
        'access_token_id' => $accessTokenId,
        'revoked' => 0,
        'expires_at' => now()->addWeek(),
    ]);
}

function actAsUserWithAccessToken(User $user, string $accessTokenId): void
{
    // Passport::actingAs stubs a fresh (unsaved) AccessToken onto
    // the user, which would leave token()->id null in the
    // controller. Build an AccessToken pointing at the seeded row
    // so revocation targets an actual DB record (AccessToken::__get
    // resolves properties through the underlying Token model).
    Passport::actingAs($user);
    auth('api')->user()->withAccessToken(new AccessToken([
        'oauth_access_token_id' => $accessTokenId,
        'oauth_user_id' => $user->getAuthIdentifier(),
        'oauth_scopes' => [],
    ]));
}

test('logout revokes the bearer access token', function () {
    $user = User::factory()->create();
    $clientId = seedOAuthClient();
    seedAccessToken('logout-token-1', $user, $clientId);

    actAsUserWithAccessToken($user, 'logout-token-1');

    $this->postJson(route('api.logout'))->assertNoContent();

    $this->assertDatabaseHas('oauth_access_tokens', [
        'id' => 'logout-token-1',
        'revoked' => 1,
    ]);
});

test('logout also revokes the paired refresh token', function () {
    $user = User::factory()->create();
    $clientId = seedOAuthClient();
    seedAccessToken('logout-token-2', $user, $clientId);
    seedRefreshToken('logout-refresh-2', 'logout-token-2');

    actAsUserWithAccessToken($user, 'logout-token-2');

    $this->postJson(route('api.logout'))->assertNoContent();

    $this->assertDatabaseHas('oauth_refresh_tokens', [
        'id' => 'logout-refresh-2',
        'revoked' => 1,
    ]);
});

test('logout does not touch the users other access tokens', function () {
    // Scope guarantee: revoking one session must not log the user
    // out of their other devices / personal-access tokens.
    $user = User::factory()->create();
    $clientId = seedOAuthClient();
    seedAccessToken('current-session-token', $user, $clientId);
    seedAccessToken('other-device-token', $user, $clientId);

    actAsUserWithAccessToken($user, 'current-session-token');

    $this->postJson(route('api.logout'))->assertNoContent();

    $this->assertDatabaseHas('oauth_access_tokens', [
        'id' => 'other-device-token',
        'revoked' => 0,
    ]);
});

test('unauthenticated request is rejected', function () {
    // Route sits behind the api middleware group's auth:api guard;
    // no bearer, no logout. Belt-and-braces coverage in case the
    // route ever gets refactored out from under the guard. A user
    // must exist so CheckForSetup does not intercept with a
    // pre-auth 302 to /setup.
    User::factory()->create();

    $this->postJson(route('api.logout'))->assertUnauthorized();
});
