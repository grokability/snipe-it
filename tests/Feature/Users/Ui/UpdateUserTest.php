<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Error;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.edit', User::factory()->create()->id))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->editUsers()->create())
            ->get(route('users.edit', User::factory()->create()->id))
            ->assertOk();
    }

    public function test_can_view_edit_page_for_soft_deleted_user()
    {
        $user = User::factory()->trashed()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('users.edit', $user->id))
            ->assertRedirectToRoute('users.show', $user->id);
    }

    public function test_users_can_be_activated_with_number()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => 0]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'activated' => 1,
            ]);

        $this->assertEquals(1, $user->refresh()->activated);
    }

    public function test_users_can_be_activated_with_boolean_true()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => false]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'activated' => true,
            ]);

        $this->assertEquals(1, $user->refresh()->activated);
    }

    public function test_users_can_be_deactivated_with_number()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'activated' => 0,
            ]);

        $this->assertEquals(0, $user->refresh()->activated);
    }

    public function test_users_can_be_deactivated_with_boolean_false()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'activated' => false,
            ]);

        $this->assertEquals(0, $user->refresh()->activated);
    }

    public function test_users_updating_themselves_do_not_deactivate_their_account()
    {
        $admin = User::factory()->editUsers()->create(['activated' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'first_name' => $admin->first_name,
                'username' => $admin->username,
            ]);

        $this->assertEquals(1, $admin->refresh()->activated);
    }

    public function test_editing_users_cannot_edit_escalation_fields_for_admins()
    {
        $editing_user = User::factory()->editUsers()->create(['activated' => true]);
        $hashed_original = Hash::make('my-awesome-password!!!!!12345');
        $admin = User::factory()->admin()->create(['username' => 'TestAdminUser', 'email' => 'admin@example.org', 'password' => $hashed_original, 'activated' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'username' => 'TestAdminUser',
            'email' => 'admin@example.org',
            'activated' => 1,
            'password' => $hashed_original,
        ]);

        $this->actingAs($editing_user)
            ->put(route('users.update', $admin), [
                'username' => 'testnewusername',
                'email' => 'testnewemail@example.org',
                'activated' => 0,
                'password' => 'TOTALLY-DIFFERENT-awesome-password!!!!!12345',
            ]);

        $this->assertEquals('TestAdminUser', $admin->refresh()->username);
        $this->assertEquals('admin@example.org', $admin->refresh()->email);
        $this->assertEquals(1, $admin->refresh()->activated);
        $this->assertNotEquals(Hash::check('super-secret', $admin->password), $admin->refresh()->password);
        $this->assertNotEquals('testnewusername', $admin->refresh()->username);
        $this->assertNotEquals('testnewemail@example.org', $admin->refresh()->email);
        $this->assertNotEquals(0, $admin->refresh()->activated);
        $this->assertNotEquals(Hash::check('TOTALLY-DIFFERENT-awesome-password!!!!!12345', $admin->password), $admin->refresh()->password);
    }

    public function test_admin_users_cannot_edit_fields_for_super_admins()
    {
        $admin = User::factory()->admin()->create(['activated' => true]);
        $hashed_original = Hash::make('my-awesome-password!!!!!12345');
        $superuser = User::factory()->superuser()->create(['username' => 'TestSuperUser', 'email' => 'superuser@example.org', 'password' => $hashed_original, 'activated' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $superuser->id,
            'username' => 'TestSuperUser',
            'email' => 'superuser@example.org',
            'activated' => 1,
            'password' => $hashed_original,
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $superuser), [
                'username' => 'testnewusername',
                'email' => 'testnewemail@example.org',
                'activated' => 0,
                'password' => 'super-secret-new-password',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $superuser->id,
            'username' => $superuser->username,
            'email' => $superuser->email,
            'activated' => $superuser->activated,
            'password' => $hashed_original,
        ]);

        $this->assertEquals('TestSuperUser', $superuser->refresh()->username);
        $this->assertEquals('superuser@example.org', $superuser->refresh()->email);
        $this->assertEquals(1, $superuser->refresh()->activated);
        $this->assertTrue(Hash::check('my-awesome-password!!!!!12345', $superuser->password), $superuser->refresh()->password);
        $this->assertNotEquals('testnewusername', $superuser->refresh()->username);
        $this->assertNotEquals('testnewemail@example.org', $superuser->refresh()->email);
        $this->assertNotTrue(Hash::check('super-secret-new-password', $superuser->password), $superuser->refresh()->password);
    }

    public function test_multi_company_user_cannot_be_moved_if_has_asset_in_different_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $companyA->id,
        ]);
        $superUser = User::factory()->superuser()->create();

        $asset = Asset::factory()->create([
            'company_id' => $companyA->id,
        ]);

        // no assets assigned, therefore success
        $this->actingAs($superUser)->put(route('users.update', $user), [
            'first_name' => 'test',
            'username' => 'test',
            'company_id' => $companyB->id,
            'redirect_option' => 'index',
        ])->assertRedirect(route('users.index'));

        $asset->checkOut($user, $superUser);

        // asset assigned, therefore error
        $response = $this->actingAs($superUser)->patchJson(route('users.update', $user), [
            'first_name' => 'test',
            'username' => 'test',
            'company_id' => $companyB->id,
            'redirect_option' => 'index',
        ]);

        $this->followRedirects($response)->assertSee('error');
    }

    public function test_multi_company_user_can_be_updated_if_has_asset_in_same_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $companyA->id,
        ]);
        $superUser = User::factory()->superuser()->create();

        $asset = Asset::factory()->create([
            'company_id' => $companyA->id,
        ]);

        // no assets assigned, therefore success
        $this->actingAs($superUser)->put(route('users.update', $user), [
            'first_name' => 'test',
            'username' => 'test',
            'company_id' => $companyA->id,
            'redirect_option' => 'index',
        ])->assertRedirect(route('users.index'));

        $asset->checkOut($user, $superUser);

        // asset assigned, therefore error
        $response = $this->actingAs($superUser)->patchJson(route('users.update', $user), [
            'first_name' => 'test',
            'username' => 'test',
            'company_id' => $companyA->id,
            'redirect_option' => 'index',
        ]);

        $this->followRedirects($response)->assertSee('success');
    }

    public function test_edit_users_permission_cannot_escalate_empty_permissions_user_to_admin_or_superuser_via_ui()
    {
        $editingUser = User::factory()->editUsers()->create();
        $targetUser = User::factory()->create([
            'permissions' => null,
        ]);

        $this->actingAs($editingUser)
            ->put(route('users.update', $targetUser), [
                'first_name' => $targetUser->first_name,
                'username' => $targetUser->username,
                'permission' => [
                    'admin' => '1',
                    'superuser' => '1',
                    'users.view' => '1',
                ],
                'redirect_option' => 'index',
            ])
            ->assertRedirect(route('users.index'));

        $decoded = (array) $targetUser->refresh()->decodePermissions();

        $this->assertArrayNotHasKey('admin', $decoded, 'Non-admin user should not be able to grant admin');
        $this->assertArrayNotHasKey('superuser', $decoded, 'Non-admin user should not be able to grant superuser');
        $this->assertEquals(1, $decoded['users.view'] ?? null, 'Non-privileged permissions should still be updateable');
    }

    public function test_admin_cannot_escalate_empty_permissions_user_to_superuser_via_ui()
    {
        $adminUser = User::factory()->admin()->create();
        $targetUser = User::factory()->create([
            'permissions' => null,
        ]);

        $this->actingAs($adminUser)
            ->put(route('users.update', $targetUser), [
                'first_name' => $targetUser->first_name,
                'username' => $targetUser->username,
                'permission' => [
                    'admin' => '1',
                    'superuser' => '1',
                ],
                'redirect_option' => 'index',
            ])
            ->assertRedirect(route('users.index'));

        $decoded = (array) $targetUser->refresh()->decodePermissions();

        $this->assertArrayHasKey('admin', $decoded, 'Admin should be able to grant admin');
        $this->assertSame('1', (string) $decoded['admin']);
        $this->assertArrayNotHasKey('superuser', $decoded, 'Admin should not be able to grant superuser');
    }

    /**
     * This can occur if the user edit screen is open in one tab and
     * the user is deleted in another before the edit form is submitted.
     *
     * @link https://app.shortcut.com/grokability/story/29166
     */
    public function test_attempting_to_update_deleted_user_is_handled_gracefully()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $user = User::factory()->for($companyA)->create();
        Asset::factory()->assignedToUser($user)->create();

        $id = $user->id;

        $user->delete();

        $response = $this->actingAs(User::factory()->editUsers()->create())
            ->put(route('users.update', $user), [
                'first_name' => 'test',
                'username' => 'test',
                'company_id' => $companyB->id,
            ]);

        $this->assertFalse($response->exceptions->contains(function ($exception) {
            // Avoid hard 500
            return $exception instanceof Error;
        }));

        // As of now, the user will be updated but not be restored
        $this->assertDatabaseHas('users', [
            'id' => $id,
            'first_name' => 'test',
            'username' => 'test',
            'company_id' => $companyB->id,
        ]);
    }
}
