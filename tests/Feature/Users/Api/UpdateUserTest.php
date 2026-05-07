<?php

namespace Tests\Feature\Users\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Department;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    public function test_can_update_user_via_patch()
    {
        $admin = User::factory()->superuser()->create();
        $manager = User::factory()->create();
        $company = Company::factory()->create();
        $department = Department::factory()->create();
        $location = Location::factory()->create();
        [$groupA, $groupB] = Group::factory()->count(2)->create();

        $user = User::factory()->create([
            'activated' => false,
            'remote' => false,
            'vip' => false,
        ]);

        $this->actingAsForApi($admin)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => 'Mabel',
                'last_name' => 'Mora',
                'username' => 'mabel',
                'password' => 'super-secret',
                'email' => 'mabel@onlymurderspod.com',
                'permissions' => '{"a.new.permission":"1"}',
                'activated' => true,
                'phone' => '619-555-5555',
                'mobile' => '619-666-6666',
                'jobtitle' => 'Host',
                'manager_id' => $manager->id,
                'employee_num' => '1111',
                'notes' => 'Pretty good artist',
                'company_id' => $company->id,
                'department_id' => $department->id,
                'location_id' => $location->id,
                'remote' => true,
                'groups' => $groupA->id,
                'vip' => true,
                'start_date' => '2021-08-01',
                'end_date' => '2025-12-31',
                'avatar' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAEsAQMAAADXeXeBAAAABlBMVEX+AAD///+KQee0AAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH5QQbCAoNcoiTQAAAACZJREFUaN7twTEBAAAAwqD1T20JT6AAAAAAAAAAAAAAAAAAAICnATvEAAEnf54JAAAAAElFTkSuQmCC',
            ])
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        $user->refresh();
        $this->assertEquals('Mabel', $user->first_name, 'First name was not updated');
        $this->assertEquals('Mora', $user->last_name, 'Last name was not updated');
        $this->assertEquals('mabel', $user->username, 'Username was not updated');
        $this->assertTrue(Hash::check('super-secret', $user->password), 'Password was not updated');
        $this->assertEquals('mabel@onlymurderspod.com', $user->email, 'Email was not updated');
        $this->assertArrayHasKey('a.new.permission', $user->decodePermissions(), 'Permissions were not updated');
        $this->assertTrue((bool) $user->activated, 'User not marked as activated');
        $this->assertEquals('619-555-5555', $user->phone, 'Phone was not updated');
        $this->assertEquals('619-666-6666', $user->mobile, 'Mobile was not updated');
        $this->assertEquals('Host', $user->jobtitle, 'Job title was not updated');
        $this->assertTrue($user->manager->is($manager), 'Manager was not updated');
        $this->assertEquals('1111', $user->employee_num, 'Employee number was not updated');
        $this->assertEquals('Pretty good artist', $user->notes, 'Notes was not updated');
        $this->assertTrue($user->company->is($company), 'Company was not updated');
        $this->assertTrue($user->department->is($department), 'Department was not updated');
        $this->assertTrue($user->location->is($location), 'Location was not updated');
        $this->assertEquals(1, $user->remote, 'Remote was not updated');
        $this->assertTrue($user->groups->contains($groupA), 'Groups were not updated');
        $this->assertEquals(1, $user->vip, 'VIP was not updated');
        $this->assertEquals('2021-08-01', $user->start_date, 'Start date was not updated');
        $this->assertEquals('2025-12-31', $user->end_date, 'End date was not updated');

        // assert against resized hash
        $this->assertEquals(
            'db2e13ba04318c99058ca429d67777322f48566b',
            sha1(Storage::disk('public')->get(app('users_upload_path').$user->avatar))
        );

        // `groups` can be an id or array or ids
        $this->patch(route('api.users.update', $user), ['groups' => [$groupA->id, $groupB->id]]);

        $user->refresh();
        $this->assertTrue($user->groups->contains($groupA), 'Not part of expected group');
        $this->assertTrue($user->groups->contains($groupB), 'Not part of expected group');
    }

    public function test_can_update_user_via_put()
    {
        $admin = User::factory()->superuser()->create();
        $manager = User::factory()->create();
        $company = Company::factory()->create();
        $department = Department::factory()->create();
        $location = Location::factory()->create();
        [$groupA, $groupB] = Group::factory()->count(2)->create();

        $user = User::factory()->create([
            'activated' => false,
            'remote' => false,
            'vip' => false,
        ]);

        $response = $this->actingAsForApi($admin)
            ->putJson(route('api.users.update', $user), [
                'first_name' => 'Mabel',
                'last_name' => 'Mora',
                'username' => 'mabel',
                'password' => 'super-secret',
                'password_confirmation' => 'super-secret',
                'email' => 'mabel@example.org',
                'permissions' => '{"a.new.permission":"1"}',
                'activated' => true,
                'phone' => '619-555-5555',
                'mobile' => '619-666-6666',
                'jobtitle' => 'Host',
                'manager_id' => $manager->id,
                'employee_num' => '1111',
                'notes' => 'Pretty good artist',
                'company_id' => $company->id,
                'department_id' => $department->id,
                'location_id' => $location->id,
                'remote' => true,
                'groups' => $groupA->id,
                'vip' => true,
                'start_date' => '2021-08-01',
                'end_date' => '2025-12-31',
                'avatar' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAEsAQMAAADXeXeBAAAABlBMVEX+AAD///+KQee0AAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH5QQbCAoNcoiTQAAAACZJREFUaN7twTEBAAAAwqD1T20JT6AAAAAAAAAAAAAAAAAAAICnATvEAAEnf54JAAAAAElFTkSuQmCC',
            ])
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        $user->refresh();
        $this->assertEquals('Mabel', $user->first_name, 'First name was not updated');
        $this->assertEquals('Mora', $user->last_name, 'Last name was not updated');
        $this->assertEquals('mabel', $user->username, 'Username was not updated');
        $this->assertTrue(Hash::check('super-secret', $user->password), 'Password was not updated');
        $this->assertEquals('mabel@example.org', $user->email, 'Email was not updated');
        $this->assertArrayHasKey('a.new.permission', $user->decodePermissions(), 'Permissions were not updated');
        $this->assertTrue((bool) $user->activated, 'User not marked as activated');
        $this->assertEquals('619-555-5555', $user->phone, 'Phone was not updated');
        $this->assertEquals('619-666-6666', $user->mobile, 'Mobile was not updated');
        $this->assertEquals('Host', $user->jobtitle, 'Job title was not updated');
        $this->assertTrue($user->manager->is($manager), 'Manager was not updated');
        $this->assertEquals('1111', $user->employee_num, 'Employee number was not updated');
        $this->assertEquals('Pretty good artist', $user->notes, 'Notes was not updated');
        $this->assertTrue($user->company->is($company), 'Company was not updated');
        $this->assertTrue($user->department->is($department), 'Department was not updated');
        $this->assertTrue($user->location->is($location), 'Location was not updated');
        $this->assertEquals(1, $user->remote, 'Remote was not updated');
        $this->assertTrue($user->groups->contains($groupA), 'Groups were not updated');
        $this->assertEquals(1, $user->vip, 'VIP was not updated');
        $this->assertEquals('2021-08-01', $user->start_date, 'Start date was not updated');
        $this->assertEquals('2025-12-31', $user->end_date, 'End date was not updated');

        // assert against resized hash
        $this->assertEquals(
            'db2e13ba04318c99058ca429d67777322f48566b',
            sha1(Storage::disk('public')->get(app('users_upload_path').$user->avatar))
        );

        // `groups` can be an id or array or ids
        $this->patch(route('api.users.update', $user), ['groups' => [$groupA->id, $groupB->id]]);

        $user->refresh();
        $this->assertTrue($user->groups->contains($groupA), 'Not part of expected group');
        $this->assertTrue($user->groups->contains($groupB), 'Not part of expected group');
    }

    public function test_api_users_can_be_activated_with_number()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => 0]);

        $this->actingAsForApi($admin)
            ->patch(route('api.users.update', $user), [
                'activated' => 1,
            ]);

        $this->assertEquals(1, $user->refresh()->activated);
    }

    public function test_api_users_can_be_activated_with_boolean_true()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => false]);

        $this->actingAsForApi($admin)
            ->patch(route('api.users.update', $user), [
                'activated' => true,
            ]);

        $this->assertEquals(1, $user->refresh()->activated);
    }

    public function test_api_users_can_be_deactivated_with_number()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => true]);

        $this->actingAsForApi($admin)
            ->patch(route('api.users.update', $user), [
                'activated' => 0,
            ]);

        $this->assertEquals(0, $user->refresh()->activated);
    }

    public function test_api_users_can_be_deactivated_with_boolean_false()
    {
        $admin = User::factory()->editUsers()->create();
        $user = User::factory()->create(['activated' => true]);

        $this->actingAsForApi($admin)
            ->patch(route('api.users.update', $user), [
                'activated' => false,
            ]);

        $this->assertEquals(0, $user->refresh()->activated);
    }

    public function test_editing_users_cannot_edit_escalation_fields_for_admins()
    {
        $hashed_original = Hash::make('!!094850394680980380kfejlskjfl');
        $hashed_new = Hash::make('!ABCDEFGIJKL123!!!');

        $editing_user = User::factory()->editUsers()->create();
        $adminuser = User::factory()->admin()->create(['username' => 'TestAdminUser', 'email' => 'admin@example.org', 'password' => $hashed_original, 'activated' => 1]);

        // The admin being edited
        $this->assertDatabaseHas('users', [
            'id' => $adminuser->id,
            'username' => 'TestAdminUser',
            'email' => 'admin@example.org',
            'activated' => 1,
            'password' => $hashed_original,
            'permissions' => '{"admin":"1"}',
        ]);

        $this->actingAsForApi($editing_user)
            ->patch(route('api.users.update', $adminuser), [
                'username' => 'testnewusername',
                'email' => 'testnewemail@example.org',
                'activated' => 0,
                'permissions' => "{'superadmin':1}",
                'password' => $hashed_new,
            ]);

        // These should keep their old values
        $this->assertEquals('TestAdminUser', $adminuser->refresh()->username);
        $this->assertEquals('admin@example.org', $adminuser->refresh()->email);
        $this->assertEquals(1, $adminuser->refresh()->activated);
        $this->assertEquals($hashed_original, $adminuser->refresh()->password);
        $this->assertEquals('{"admin":"1"}', $adminuser->refresh()->permissions);

    }

    public function test_admins_cannot_deescalate_superadmins()
    {
        $hashed_original = Hash::make('my-awesome-password!!!!!12345');
        $hashed_new = Hash::make('!ABCDEFGIJKL123!!!');

        $editing_user = User::factory()->admin()->create();
        $superuser = User::factory()->superuser()->create(['username' => 'TestSuperUser', 'email' => 'superuser@example.org', 'password' => $hashed_original, 'activated' => 1]);

        // The admin being edited
        $this->assertDatabaseHas('users', [
            'id' => $superuser->id,
            'username' => 'TestSuperUser',
            'email' => 'superuser@example.org',
            'activated' => 1,
            'password' => $hashed_original,
            'permissions' => '{"superuser":"1"}',
        ]);

        $this->actingAsForApi($editing_user)
            ->patch(route('api.users.update', $superuser), [
                'username' => 'testnewusername',
                'email' => 'testnewemail@example.org',
                'activated' => 0,
                'permissions' => '{"admin":"1"}',
                'password' => $hashed_new,
            ]);

        // These should keep their old values
        $this->assertEquals('TestSuperUser', $superuser->refresh()->username);
        $this->assertEquals('superuser@example.org', $superuser->refresh()->email);
        $this->assertEquals(1, $superuser->refresh()->activated);
        $this->assertEquals($hashed_original, $superuser->refresh()->password);
        $this->assertEquals('{"superuser":"1"}', $superuser->refresh()->permissions);
        $this->assertNotEquals('testnewusername', $superuser->refresh()->username);
        $this->assertNotEquals('testnewemail@example.org', $superuser->refresh()->email);
        $this->assertNotTrue(Hash::check('super-secret-new-password', $superuser->password), $superuser->refresh()->password);

    }

    public function test_users_scoped_to_company_during_update_when_multiple_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create(['name' => 'Company A']);
        $companyB = Company::factory()->create(['name' => 'Company B']);

        $adminA = User::factory(['company_id' => $companyA->id])->admin()->create();
        $adminB = User::factory(['company_id' => $companyB->id])->admin()->create();
        $adminNoCompany = User::factory(['company_id' => null])->admin()->create();

        // Create users that belongs to company A and B and one that is unscoped
        $scoped_user_in_companyA = User::factory()->create(['company_id' => $companyA->id]);
        $scoped_user_in_companyB = User::factory()->create(['company_id' => $companyB->id]);
        $scoped_user_in_no_company = User::factory()->create(['company_id' => null]);

        // Admin for Company A should allow updating user from Company A
        $this->actingAsForApi($adminA)
            ->patchJson(route('api.users.update', $scoped_user_in_companyA))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        // Admin for Company A should get denied updating user from Company B
        $this->actingAsForApi($adminA)
            ->patchJson(route('api.users.update', $scoped_user_in_companyB))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        // Admin for Company A should get denied updating user without a company
        $this->actingAsForApi($adminA)
            ->patchJson(route('api.users.update', $scoped_user_in_no_company))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        // Admin for Company B should allow updating user from Company B
        $this->actingAsForApi($adminB)
            ->patchJson(route('api.users.update', $scoped_user_in_companyB))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        // Admin for Company B should get denied updating user from Company A
        $this->actingAsForApi($adminB)
            ->patchJson(route('api.users.update', $scoped_user_in_companyA))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        // Admin for Company B should get denied updating user without a company
        $this->actingAsForApi($adminB)
            ->patchJson(route('api.users.update', $scoped_user_in_no_company))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        // Admin without a company should allow updating user without a company
        $this->actingAsForApi($adminNoCompany)
            ->patchJson(route('api.users.update', $scoped_user_in_no_company))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        // Admin without a company should get denied updating user from Company A
        $this->actingAsForApi($adminNoCompany)
            ->patchJson(route('api.users.update', $scoped_user_in_companyA))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        // Admin without a company should get denied updating user from Company B
        $this->actingAsForApi($adminNoCompany)
            ->patchJson(route('api.users.update', $scoped_user_in_companyB))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_user_groups_are_only_updated_if_authenticated_user_is_super_user()
    {
        $groupToJoin = Group::factory()->create();

        $userWhoCanEditUsers = User::factory()->editUsers()->create();
        $superUser = User::factory()->superuser()->create();

        $userToUpdateByUserWhoCanEditUsers = User::factory()->create();
        $userToUpdateByToUserBySuperuser = User::factory()->create();

        $this->actingAsForApi($userWhoCanEditUsers)
            ->patchJson(route('api.users.update', $userToUpdateByUserWhoCanEditUsers), [
                'groups' => [$groupToJoin->id],
            ]);

        $this->actingAsForApi($superUser)
            ->patchJson(route('api.users.update', $userToUpdateByToUserBySuperuser), [
                'groups' => [$groupToJoin->id],
            ]);

        $this->assertFalse($userToUpdateByUserWhoCanEditUsers->refresh()->groups->contains($groupToJoin),
            'Non-super-user was able to modify user group'
        );

        $this->assertTrue($userToUpdateByToUserBySuperuser->refresh()->groups->contains($groupToJoin));
    }

    public function test_user_groups_can_be_cleared_by_super_user()
    {
        $normalUser = User::factory()->editUsers()->create();
        $superUser = User::factory()->superuser()->create();

        $oneUserToUpdate = User::factory()->create();
        $anotherUserToUpdate = User::factory()->create();

        $joinedGroup = Group::factory()->create();
        $oneUserToUpdate->groups()->sync([$joinedGroup->id]);
        $anotherUserToUpdate->groups()->sync([$joinedGroup->id]);

        $this->actingAsForApi($normalUser)
            ->patchJson(route('api.users.update', $oneUserToUpdate), [
                'groups' => null,
            ]);

        $this->actingAsForApi($superUser)
            ->patchJson(route('api.users.update', $anotherUserToUpdate), [
                'groups' => null,
            ]);

        $this->assertTrue($oneUserToUpdate->refresh()->groups->contains($joinedGroup));
        $this->assertFalse($anotherUserToUpdate->refresh()->groups->contains($joinedGroup));
    }

    public function test_non_superuser_cannot_update_own_groups()
    {
        $groupToJoin = Group::factory()->create();
        $user = User::factory()->editUsers()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.users.update', $user), [
                'groups' => [$groupToJoin->id],
            ]);

        $this->assertFalse($user->refresh()->groups->contains($groupToJoin),
            'Non-super-user was able to modify user group'
        );

    }

    public function test_non_superuser_cannot_update_groups()
    {
        $user = User::factory()->editUsers()->create();
        $group = Group::factory()->create();
        $user->groups()->sync([$group->id]);
        $newGroupToJoin = Group::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.users.update', $user), [
                'groups' => [$newGroupToJoin->id],
            ]);

        $this->assertFalse($user->refresh()->groups->contains($newGroupToJoin),
            'Non-super-user was able to modify user group membership'
        );

        $this->assertTrue($user->refresh()->groups->contains($group));

    }

    public function test_users_groups_are_not_cleared_if_no_group_passed_by_super_user()
    {
        $user = User::factory()->create();
        $superUser = User::factory()->superuser()->create();

        $group = Group::factory()->create();
        $user->groups()->sync([$group->id]);

        $this->actingAsForApi($superUser)
            ->patchJson(route('api.users.update', $user), []);

        $this->assertTrue($user->refresh()->groups->contains($group));
    }

    public function test_multiple_groups_update_by_super_user()
    {
        $user = User::factory()->create();
        $superUser = User::factory()->superuser()->create();

        $groupA = Group::factory()->create(['name' => 'Group A']);
        $groupB = Group::factory()->create(['name' => 'Group B']);

        $this->actingAsForApi($superUser)
            ->patchJson(route('api.users.update', $user), [
                'groups' => [$groupA->id, $groupB->id],
            ])->json();

        $this->assertTrue($user->refresh()->groups->contains($groupA));
        $this->assertTrue($user->refresh()->groups->contains($groupB));
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
        $this->actingAsForApi($superUser)->patchJson(route('api.users.update', $user), [
            'username' => 'test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('success');

        // same test but PUT
        $this->actingAsForApi($superUser)->putJson(route('api.users.update', $user), [
            'username' => 'test',
            'first_name' => 'Test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('success');

        $asset->checkOut($user, $superUser);

        // asset assigned, therefore error
        $this->actingAsForApi($superUser)->patchJson(route('api.users.update', $user), [
            'username' => 'test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('error');

        // same test but PUT
        $this->actingAsForApi($superUser)->putJson(route('api.users.update', $user), [
            'username' => 'test',
            'first_name' => 'Test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('error');
    }

    public function test_multi_company_user_can_be_updated_if_has_asset_in_same_company()
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

        // no assets assigned from other company, therefore success
        $this->actingAsForApi($superUser)->patchJson(route('api.users.update', $user), [
            'username' => 'test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('success');

        // same test but PUT
        $this->actingAsForApi($superUser)->putJson(route('api.users.update', $user), [
            'username' => 'test',
            'first_name' => 'Test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('success');

        $asset->checkOut($user, $superUser);

        // asset assigned from other company, therefore error
        $this->actingAsForApi($superUser)->patchJson(route('api.users.update', $user), [
            'username' => 'test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('error');

        // same test but PUT
        $this->actingAsForApi($superUser)->putJson(route('api.users.update', $user), [
            'username' => 'test',
            'first_name' => 'Test',
            'company_id' => $companyB->id,
        ])->assertStatusMessageIs('error');
    }

    public function test_edit_users_permission_cannot_escalate_empty_permissions_user_to_admin_or_superuser_via_api()
    {
        $editingUser = User::factory()->editUsers()->create();
        $targetUser = User::factory()->create([
            'permissions' => null,
        ]);

        $this->actingAsForApi($editingUser)
            ->putJson(route('api.users.update', $targetUser), [
                'first_name' => $targetUser->first_name,
                'username' => $targetUser->username,
                'permissions' => [
                    'admin' => '1',
                    'superuser' => '1',
                    'users.view' => '1',
                ],
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $decoded = (array) $targetUser->refresh()->decodePermissions();

        $this->assertArrayNotHasKey('admin', $decoded, 'Non-admin user should not be able to grant admin');
        $this->assertArrayNotHasKey('superuser', $decoded, 'Non-admin user should not be able to grant superuser');
        $this->assertEquals(1, $decoded['users.view'] ?? null, 'Non-privileged permissions should still be updateable');
    }

    public function test_admin_cannot_escalate_empty_permissions_user_to_superuser_via_api()
    {
        $adminUser = User::factory()->admin()->create();
        $targetUser = User::factory()->create([
            'permissions' => null,
        ]);

        $this->actingAsForApi($adminUser)
            ->putJson(route('api.users.update', $targetUser), [
                'first_name' => $targetUser->first_name,
                'username' => $targetUser->username,
                'permissions' => [
                    'admin' => '1',
                    'superuser' => '1',
                ],
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $decoded = (array) $targetUser->refresh()->decodePermissions();

        $this->assertArrayHasKey('admin', $decoded, 'Admin should be able to grant admin');
        $this->assertSame('1', (string) $decoded['admin']);
        $this->assertArrayNotHasKey('superuser', $decoded, 'Admin should not be able to grant superuser');
    }
}
