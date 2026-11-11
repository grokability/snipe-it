<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Coverage for POST /api/v1/logout, the bearer-authenticated
 * self-revoke endpoint added because Passport does not ship one (its
 * built-in DELETE /oauth/tokens/{id} is session-cookie authenticated
 * for the Passport-Vue self-service UI, and RFC 7009 revocation
 * expects client credentials rather than bearer auth). The endpoint
 * revokes the access token that authenticated THIS request and any
 * refresh token issued alongside it, but must leave the user's other
 * tokens (other devices, personal-access tokens, tokens under other
 * OAuth clients) untouched.
 */
class ApiLogoutTest extends TestCase
{
    private function seedOAuthClient(): int
    {
        return DB::table('oauth_clients')->insertGetId([
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
    }

    private function seedAccessToken(string $id, User $user, int $clientId): void
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

    private function seedRefreshToken(string $id, string $accessTokenId): void
    {
        DB::table('oauth_refresh_tokens')->insert([
            'id' => $id,
            'access_token_id' => $accessTokenId,
            'revoked' => 0,
            'expires_at' => now()->addWeek(),
        ]);
    }

    private function actAsUserWithAccessToken(User $user, string $accessTokenId): void
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

    public function test_logout_revokes_the_bearer_access_token(): void
    {
        $user = User::factory()->create();
        $clientId = $this->seedOAuthClient();
        $this->seedAccessToken('logout-token-1', $user, $clientId);

        $this->actAsUserWithAccessToken($user, 'logout-token-1');

        $this->postJson(route('api.logout'))->assertNoContent();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => 'logout-token-1',
            'revoked' => 1,
        ]);
    }

    public function test_logout_also_revokes_the_paired_refresh_token(): void
    {
        $user = User::factory()->create();
        $clientId = $this->seedOAuthClient();
        $this->seedAccessToken('logout-token-2', $user, $clientId);
        $this->seedRefreshToken('logout-refresh-2', 'logout-token-2');

        $this->actAsUserWithAccessToken($user, 'logout-token-2');

        $this->postJson(route('api.logout'))->assertNoContent();

        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => 'logout-refresh-2',
            'revoked' => 1,
        ]);
    }

    public function test_logout_does_not_touch_the_users_other_access_tokens(): void
    {
        // Scope guarantee: revoking one session must not log the user
        // out of their other devices / personal-access tokens.
        $user = User::factory()->create();
        $clientId = $this->seedOAuthClient();
        $this->seedAccessToken('current-session-token', $user, $clientId);
        $this->seedAccessToken('other-device-token', $user, $clientId);

        $this->actAsUserWithAccessToken($user, 'current-session-token');

        $this->postJson(route('api.logout'))->assertNoContent();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => 'other-device-token',
            'revoked' => 0,
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        // Route sits behind the api middleware group's auth:api guard;
        // no bearer, no logout. Belt-and-braces coverage in case the
        // route ever gets refactored out from under the guard. A user
        // must exist so CheckForSetup does not intercept with a
        // pre-auth 302 to /setup.
        User::factory()->create();

        $this->postJson(route('api.logout'))->assertUnauthorized();
    }
}
