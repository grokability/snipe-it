<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        $clientId = DB::table('oauth_clients')->insertGetId([
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
}
