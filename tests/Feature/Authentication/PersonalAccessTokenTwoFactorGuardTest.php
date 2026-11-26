<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Regression: a password-only session that lands on /two-factor (exempt from
 * CheckForTwoFactor) used to pick up the Passport `snipeit_passport_token`
 * cookie and then mint a persistent personal-access-token via the API
 * group, fully bypassing the second factor. ProfileController endpoints
 * now refuse when the session hasn't cleared 2FA, and the wrapper
 * IssueFreshApiTokenIfTwoFactorComplete refuses to issue the Passport
 * cookie in the same state.
 */
class PersonalAccessTokenTwoFactorGuardTest extends TestCase
{
    private function seedPassportPersonalAccessClient(): void
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

    public function test_cannot_create_personal_access_token_before_completing_two_factor()
    {
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
    }

    public function test_cannot_list_personal_access_tokens_before_completing_two_factor()
    {
        $this->settings->set(['two_factor_enabled' => '2']);
        $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create([
            'two_factor_optin' => '1',
            'two_factor_enrolled' => '1',
            'two_factor_secret' => 'TESTSECRET',
        ]);

        $this->actingAs($user, 'api')
            ->getJson(route('api.personal-access-token.index'))
            ->assertForbidden();
    }

    public function test_can_create_personal_access_token_after_completing_two_factor()
    {
        $this->seedPassportPersonalAccessClient();
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
    }

    public function test_can_create_personal_access_token_when_two_factor_is_disabled()
    {
        // Setting "two_factor_enabled" empty == disabled globally; the guard
        // returns true so existing installs without 2FA are unaffected.
        $this->seedPassportPersonalAccessClient();
        $this->settings->set(['two_factor_enabled' => '']);
        $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create();

        $this->actingAs($user, 'api')
            ->postJson(route('api.personal-access-token.create'), ['name' => 'no2fa'])
            ->assertOk();
    }

    public function test_optional_two_factor_user_not_opted_in_can_still_create_token()
    {
        // 2FA optional, user hasn't opted in — they shouldn't be blocked.
        $this->seedPassportPersonalAccessClient();
        $this->settings->set(['two_factor_enabled' => '1']);
        $user = User::factory()->state(fn () => ['permissions' => json_encode(['self.api' => '1'])])->create(['two_factor_optin' => '0']);

        $this->actingAs($user, 'api')
            ->postJson(route('api.personal-access-token.create'), ['name' => 'optout'])
            ->assertOk();
    }

    public function test_bearer_token_can_manage_personal_tokens_when_two_factor_is_disabled(): void
    {
        $this->settings->set(['two_factor_enabled' => '']);
        $this->seedPassportPersonalAccessClient();
        $user = User::factory()->selfApi()->create();
        $token = $user->createToken('existing');

        // Exercise the API without the test environment's automatic CSRF bypass.
        $this->app->instance('env', 'local');
        $this->withHeader('Authorization', 'Bearer '.$token->accessToken);

        $this->getJson(route('api.personal-access-token.index'))
            ->assertOk()
            ->assertJsonPath('payload.0.id', $token->token->id);
        $response = $this->postJson(route('api.personal-access-token.create'), ['name' => 'created'])
            ->assertOk();
        $createdTokenId = $response->json('payload.id');
        $this->assertNotNull($createdTokenId);
        $this->deleteJson(route('api.personal-access-token.delete', $createdTokenId))->assertNoContent();

        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $createdTokenId, 'revoked' => true]);
        $this->assertFalse($token->token->fresh()->revoked);
        $this->getJson(route('api.users.me'))->assertOk();
    }

    public function test_api_user_without_self_api_permission_cannot_manage_personal_tokens(): void
    {
        $this->settings->set(['two_factor_enabled' => '']);
        $this->actingAsForApi(User::factory()->create());

        $this->getJson(route('api.personal-access-token.index'))->assertForbidden();
        $this->postJson(route('api.personal-access-token.create'), ['name' => 'denied'])->assertForbidden();
        $this->deleteJson(route('api.personal-access-token.delete', 'any-token'))->assertForbidden();
    }

    public function test_cannot_delete_personal_token_before_completing_two_factor(): void
    {
        $this->settings->set(['two_factor_enabled' => '2']);
        $user = User::factory()->selfApi()->create([
            'two_factor_optin' => '1',
            'two_factor_enrolled' => '1',
            'two_factor_secret' => 'TESTSECRET',
        ]);

        $this->actingAs($user, 'api')
            ->deleteJson(route('api.personal-access-token.delete', 'any-token'))
            ->assertForbidden();
    }

    public function test_can_list_and_delete_personal_token_after_completing_two_factor(): void
    {
        $this->settings->set(['two_factor_enabled' => '2']);
        $this->seedPassportPersonalAccessClient();
        $user = User::factory()->selfApi()->create([
            'two_factor_optin' => '1',
            'two_factor_enrolled' => '1',
            'two_factor_secret' => 'TESTSECRET',
        ]);
        $token = $user->createToken('existing')->token;

        $this->actingAs($user, 'api')->withSession(['2fa_authed' => $user->id]);

        $this->getJson(route('api.personal-access-token.index'))
            ->assertOk()
            ->assertJsonPath('payload.0.id', $token->id);
        $this->deleteJson(route('api.personal-access-token.delete', $token->id))->assertNoContent();
        $this->assertTrue($token->fresh()->revoked);
    }

    public function test_bearer_token_without_completed_two_factor_session_cannot_manage_personal_tokens(): void
    {
        $this->settings->set(['two_factor_enabled' => '2']);
        $this->seedPassportPersonalAccessClient();
        $user = User::factory()->selfApi()->create([
            'two_factor_optin' => '1',
            'two_factor_enrolled' => '1',
            'two_factor_secret' => 'TESTSECRET',
        ]);
        $token = $user->createToken('existing');

        $this->withHeader('Authorization', 'Bearer '.$token->accessToken);

        $this->getJson(route('api.personal-access-token.index'))->assertForbidden();
        $this->postJson(route('api.personal-access-token.create'), ['name' => 'denied'])
            ->assertForbidden();
        $this->deleteJson(route('api.personal-access-token.delete', $token->token->id))->assertForbidden();

        $this->assertDatabaseMissing('oauth_access_tokens', ['name' => 'denied']);
        $this->assertFalse($token->token->fresh()->revoked);
    }

    public function test_browser_login_cookie_authenticates_ordinary_api_without_a_session(): void
    {
        // Disabled 2FA is stored as null; MySQL coerces an empty string to zero.
        $this->settings->set(['two_factor_enabled' => null]);
        $user = User::factory()->selfApi()->create();

        $this->post(route('login'), ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $response = $this->get(route('user.api'))->assertOk();
        $cookie = $response->getCookie(Passport::cookie(), false);
        $this->assertNotNull($cookie);
        $csrfToken = session()->token();

        $this->app['auth']->forgetGuards();

        $this->withCredentials()
            ->withUnencryptedCookie(Passport::cookie(), $cookie->getValue())
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->getJson(route('api.users.me'))
            ->assertOk()
            ->assertCookieMissing(config('session.cookie'));

        $this->assertFalse($this->app['request']->hasSession());
    }

    public function test_browser_cookies_preserve_completed_two_factor_for_api_token_creation(): void
    {
        config(['session.driver' => 'cookie']);
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');
        $this->app['auth']->forgetGuards();
        $this->settings->set(['two_factor_enabled' => '2']);
        $this->seedPassportPersonalAccessClient();
        $user = User::factory()->selfApi()->create([
            'two_factor_optin' => '1',
            'two_factor_enrolled' => '1',
            'two_factor_secret' => 'TESTSECRET',
        ]);

        $this->app['session']->driver()->setRequestOnHandler($this->app['request']);
        $this->actingAs($user)->withSession(['2fa_authed' => $user->id]);
        $page = $this->get(route('user.api'))->assertOk();
        $sessionId = session()->getId();
        $csrfToken = session()->token();
        $cookies = [];
        foreach ($page->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }
        $this->assertArrayHasKey(Passport::cookie(), $cookies);
        $this->assertArrayHasKey($sessionId, $cookies);

        // The next request must recover identity and 2FA solely from browser cookies.
        $this->app['auth']->forgetGuards();
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');
        $this->app['cookie']->flushQueuedCookies();

        $response = $this->withCredentials()
            ->withUnencryptedCookies($cookies)
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->postJson(route('api.personal-access-token.create'), ['name' => 'browser-cookie-token'])
            ->assertOk();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'name' => 'browser-cookie-token',
            'user_id' => $user->id,
        ]);
        $this->assertSame($user->id, $this->app['request']->session()->get('2fa_authed'));
        $payloadCookie = $response->getCookie($sessionId);
        $this->assertNotNull($payloadCookie);
        $this->assertStringContainsString('2fa_authed', $payloadCookie->getValue());
        $encryptedPayloadCookie = $response->getCookie($sessionId, false);
        $this->assertNotNull($encryptedPayloadCookie);
        $this->assertStringNotContainsString('2fa_authed', $encryptedPayloadCookie->getValue());

        $this->app['auth']->forgetGuards();
        $this->withHeader('X-CSRF-TOKEN', 'invalid')
            ->getJson(route('api.personal-access-token.index'))
            ->assertUnauthorized();
    }
}
