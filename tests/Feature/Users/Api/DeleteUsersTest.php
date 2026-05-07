<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteUsersTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertForbidden();

        $this->assertNotSoftDeleted($user);
    }

    public function test_error_returned_via_api_if_user_does_not_exist()
    {
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', 'invalid-id'))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_error_returned_via_api_if_user_is_already_deleted()
    {
        $user = User::factory()->deletedUser()->create();
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_disallow_user_deletion_via_api_if_still_managing_people()
    {
        $manager = User::factory()->create();
        User::factory()->count(5)->create(['manager_id' => $manager->id]);
        $this->assertFalse($manager->isDeletable());

        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $manager))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_disallow_user_deletion_via_api_if_still_managing_locations()
    {
        $manager = User::factory()->create();
        Location::factory()->count(5)->create(['manager_id' => $manager->id]);

        $this->assertFalse($manager->isDeletable());

        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $manager))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_disallow_user_deletion_via_api_if_still_has_licenses()
    {
        $manager = User::factory()->create();
        LicenseSeat::factory()->count(5)->create(['assigned_to' => $manager->id]);

        $this->assertFalse($manager->isDeletable());

        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $manager))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_users_cannot_delete_themselves()
    {
        $user = User::factory()->deleteUsers()->create();
        $this->actingAsForApi($user)
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $userFromA = User::factory()->deleteUsers()->for($companyA)->create();
        $userFromB = User::factory()->deleteUsers()->for($companyB)->create();

        $this->actingAsForApi($userFromA)
            ->deleteJson(route('api.users.destroy', $userFromB))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        $userFromB->refresh();
        $this->assertNull($userFromB->deleted_at);

        $this->actingAsForApi($userFromB)
            ->deleteJson(route('api.users.destroy', $userFromA))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        $userFromA->refresh();
        $this->assertNull($userFromA->deleted_at);

        $this->actingAsForApi($superuser)
            ->deleteJson(route('api.users.destroy', $userFromA))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        $userFromA->refresh();
        $this->assertNotNull($userFromA->deleted_at);
    }

    public function test_can_delete_user()
    {
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($user);
    }

    public function test_deleting_user_revokes_associated_passport_tokens()
    {
        $user = User::factory()->create();

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

        DB::table('oauth_access_tokens')->insert([
            'id' => 'soft-delete-token-'.$user->id,
            'user_id' => $user->id,
            'client_id' => $clientId,
            'name' => 'Soft Delete Token',
            'scopes' => '[]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        DB::table('oauth_refresh_tokens')->insert([
            'id' => 'soft-delete-refresh-'.$user->id,
            'access_token_id' => 'soft-delete-token-'.$user->id,
            'revoked' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => 'soft-delete-token-'.$user->id,
            'revoked' => 1,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => 'soft-delete-refresh-'.$user->id,
            'revoked' => 1,
        ]);
    }

    public function test_force_deleting_user_hard_deletes_associated_passport_tokens()
    {
        $user = User::factory()->create();

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

        DB::table('oauth_access_tokens')->insert([
            'id' => 'force-delete-token-'.$user->id,
            'user_id' => $user->id,
            'client_id' => $clientId,
            'name' => 'Force Delete Token',
            'scopes' => '[]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        DB::table('oauth_refresh_tokens')->insert([
            'id' => 'force-delete-refresh-'.$user->id,
            'access_token_id' => 'force-delete-token-'.$user->id,
            'revoked' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        $user->forceDelete();

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'id' => 'force-delete-token-'.$user->id,
        ]);
        $this->assertDatabaseMissing('oauth_refresh_tokens', [
            'id' => 'force-delete-refresh-'.$user->id,
        ]);
    }

    public function test_admin_cannot_delete_super_user()
    {
        $superuser = User::factory()->superuser()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAsForApi($admin)
            ->deleteJson(route('api.users.destroy', $superuser))
            ->assertOk()
            ->assertStatusMessageIs('error');

    }

    public function test_user_cannot_delete_admin_user()
    {
        $user = User::factory()->deleteUsers()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAsForApi($user)
            ->deleteJson(route('api.users.destroy', $admin))
            ->assertOk()
            ->assertStatusMessageIs('error');

    }

    public function test_user_cannot_delete_super_user()
    {
        $user = User::factory()->deleteUsers()->create();
        $superuser = User::factory()->superuser()->create();

        $this->actingAsForApi($user)
            ->deleteJson(route('api.users.destroy', $superuser))
            ->assertOk()
            ->assertStatusMessageIs('error');

    }
}
