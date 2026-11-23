<?php

namespace Tests\Feature\Settings;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OauthSettingsTest extends TestCase
{
    public function test_superuser_can_view_personal_access_tokens_table(): void
    {
        $superuser = User::factory()->superuser()->create();
        $tokenOwner = User::factory()->create([
            'username' => 'token-owner', 'first_name' => 'Token',
            'last_name' => 'Owner',
            'display_name' => 'Token Owner Display',
        ]);
        $createdAt = now()->subHour();
        $expiresAt = now()->addDay();

        $personalClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $personalClientId,
            'user_id' => null,
            'name' => 'Personal Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nonPersonalClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $nonPersonalClientId,
            'user_id' => null,
            'name' => 'OAuth Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_access_tokens')->insert([
            [
                'id' => 'personal-token-1',
                'user_id' => $tokenOwner->id,
                'client_id' => $personalClientId,
                'name' => 'Personal Token One',
                'scopes' => '[]',
                'revoked' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'expires_at' => $expiresAt,
            ],
            [
                'id' => 'oauth-token-1',
                'user_id' => $tokenOwner->id,
                'client_id' => $nonPersonalClientId,
                'name' => 'Non Personal Token',
                'scopes' => '[]',
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDay(),
            ],
        ]);

        $this->actingAs($superuser)
            ->get(route('settings.oauth.index'))
            ->assertOk()
            ->assertSee(trans('admin/settings/general.oauth_personal_access_tokens'))
            ->assertSee('Personal Token One')
            ->assertSee('Token Owner Display')
            ->assertSee(route('users.show', $tokenOwner))
            ->assertSee(Helper::getFormattedDateObject($createdAt, 'datetime', false))
            ->assertSee(Helper::getFormattedDateObject($expiresAt, 'datetime', false))
            ->assertDontSee('Non Personal Token');
    }

    public function test_personal_access_token_status_shows_active_revoked_or_expired(): void
    {
        $superuser = User::factory()->superuser()->create();

        $personalClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $personalClientId,
            'user_id' => null,
            'name' => 'Personal Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_access_tokens')->insert([
            [
                'id' => 'status-active-token',
                'user_id' => $superuser->id,
                'client_id' => $personalClientId,
                'name' => 'Active Status Token',
                'scopes' => '[]',
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDay(),
            ],
            [
                'id' => 'status-revoked-token',
                'user_id' => $superuser->id,
                'client_id' => $personalClientId,
                'name' => 'Revoked Status Token',
                'scopes' => '[]',
                'revoked' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDay(),
            ],
            [
                'id' => 'status-expired-token',
                'user_id' => $superuser->id,
                'client_id' => $personalClientId,
                'name' => 'Expired Status Token',
                'scopes' => '[]',
                'revoked' => 0,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
                'expires_at' => now()->subDay(),
            ],
        ]);

        $this->actingAs($superuser)
            ->get(route('settings.oauth.index'))
            ->assertOk()
            ->assertSee(trans('admin/settings/general.oauth_token_status_active'))
            ->assertSee(trans('admin/settings/general.oauth_token_status_revoked'))
            ->assertSee(trans('admin/settings/general.oauth_token_status_expired'));
    }

    public function test_permission_required_to_view_oauth_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.oauth.index'))
            ->assertForbidden();
    }

    public function test_soft_deleted_token_owner_display_name_is_shown_with_strikethrough(): void
    {
        $superuser = User::factory()->superuser()->create();
        $tokenOwner = User::factory()->create([
            'username' => 'deleted-token-owner',
            'display_name' => 'Deleted Token User',
        ]);
        $tokenOwner->delete();

        $personalClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $personalClientId,
            'user_id' => null,
            'name' => 'Personal Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_access_tokens')->insert([
            'id' => 'soft-deleted-owner-token',
            'user_id' => $tokenOwner->id,
            'client_id' => $personalClientId,
            'name' => 'Soft Deleted Owner Token',
            'scopes' => '[]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($superuser)
            ->get(route('settings.oauth.index'))
            ->assertOk()
            ->assertSee('<del>Deleted Token User</del>', false)
            ->assertSee(route('users.show', $tokenOwner));
    }

    // Authorized Applications tab was removed - the OAuth Clients +
    // Pending OAuth Clients tabs now cover every admin workflow it
    // used to surface, and deleting an OAuth client cascades token
    // revocation via Passport's ClientRepository::delete anyway, so
    // the derived "clients with active tokens" view was redundant.

    public function test_oauth_clients_table_shows_associated_token_count_per_client_id(): void
    {
        $superuser = User::factory()->superuser()->create();

        $ownedClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $ownedClientId,
            'user_id' => $superuser->id,
            'name' => 'Owned OAuth Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 0,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $otherClientId,
            'user_id' => User::factory()->create()->id,
            'name' => 'Other OAuth Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 0,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_clients')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => null,
            'name' => 'Personal Access Legacy Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_clients')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => null,
            'name' => 'Password Grant Legacy Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_clients')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => null,
            'name' => 'Revoked OAuth Legacy Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 0,
            'password_client' => 0,
            'revoked' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_access_tokens')->insert([
            [
                'id' => 'owned-client-token-1',
                'user_id' => $superuser->id,
                'client_id' => $ownedClientId,
                'name' => 'Owned Client Token One',
                'scopes' => '[]',
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDay(),
            ],
            [
                'id' => 'owned-client-token-2',
                'user_id' => $superuser->id,
                'client_id' => $ownedClientId,
                'name' => 'Owned Client Token Two',
                'scopes' => '[]',
                'revoked' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDay(),
            ],
            [
                'id' => 'other-client-token-1',
                'user_id' => $superuser->id,
                'client_id' => $otherClientId,
                'name' => 'Other Client Token One',
                'scopes' => '[]',
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDay(),
            ],
        ]);

        $this->actingAs($superuser)
            ->get(route('settings.oauth.index'))
            ->assertOk()
            ->assertSee(trans('admin/settings/general.oauth_associated_token_count'))
            ->assertSee(trans('admin/settings/general.oauth_client_type'))
            ->assertSee('Owned OAuth Client')
            // Personal Access clients are Passport internal plumbing
            // for the PAT grant and the Personal Access Tokens tab
            // owns their management, so the Approved OAuth Clients tab
            // deliberately excludes them.
            ->assertDontSee('Personal Access Legacy Client')
            ->assertSee('Password Grant Legacy Client')
            // Revoked clients hide behind the Show Revoked toggle by
            // default.
            ->assertDontSee('Revoked OAuth Legacy Client')
            ->assertSee(trans('admin/settings/general.oauth_client_type_oauth'))
            ->assertSee(trans('admin/settings/general.oauth_client_type_password_grant'))
            ->assertSee('data-field="associated_token_count"', false)
            ->assertSee('data-field="client_type"', false)
            ->assertSeeInOrder(['Owned OAuth Client', '<td>2</td>'], false)
            ->assertDontSee('Other Client Token One');
    }

    public function test_superuser_can_revoke_and_unrevoke_personal_access_token(): void
    {
        $superuser = User::factory()->superuser()->create();

        $personalClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $personalClientId,
            'user_id' => null,
            'name' => 'Personal Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_access_tokens')->insert([
            'id' => 'toggle-token-1',
            'user_id' => $superuser->id,
            'client_id' => $personalClientId,
            'name' => 'Toggle Token',
            'scopes' => '[]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($superuser)
            ->post(route('settings.oauth.tokens.revoke', ['token' => 'toggle-token-1']))
            ->assertRedirect(route('settings.oauth.index').'#personal-access-tokens');

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => 'toggle-token-1',
            'revoked' => 1,
        ]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => User::class,
            'item_id' => $superuser->id,
            'target_type' => User::class,
            'target_id' => $superuser->id,
            'created_by' => $superuser->id,
            'action_type' => 'token revoked',
        ]);

        $this->actingAs($superuser)
            ->post(route('settings.oauth.tokens.unrevoke', ['token' => 'toggle-token-1']))
            ->assertRedirect(route('settings.oauth.index').'#personal-access-tokens');

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => 'toggle-token-1',
            'revoked' => 0,
        ]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => User::class,
            'item_id' => $superuser->id,
            'target_type' => User::class,
            'target_id' => $superuser->id,
            'created_by' => $superuser->id,
            'action_type' => 'token unrevoked',
        ]);
    }

    public function test_permission_required_to_toggle_oauth_token_state(): void
    {
        $personalClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id' => $personalClientId,
            'user_id' => null,
            'name' => 'Personal Client',
            'secret' => 'secret',
            'redirect' => 'http://localhost/callback',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_access_tokens')->insert([
            'id' => 'toggle-permission-token-1',
            'user_id' => User::factory()->create()->id,
            'client_id' => $personalClientId,
            'name' => 'Permission Token',
            'scopes' => '[]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('settings.oauth.tokens.revoke', ['token' => 'toggle-permission-token-1']))
            ->assertForbidden();
    }
}
