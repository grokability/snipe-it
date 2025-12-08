<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Error;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UpdateUserTest extends TestCase
{

    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.edit', User::factory()->create()->id))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->editUsers()->create())
            ->get(route('users.edit', User::factory()->create()->id))
            ->assertOk();
    }

    public function testCanViewEditPageForSoftDeletedUser()
    {
        $user = User::factory()->trashed()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('users.edit', $user->id))
            ->assertRedirectToRoute('users.show', $user->id);
    }

    public function testUsersCanBeActivatedWithNumber()
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

    public function testUsersCanBeActivatedWithBooleanTrue()
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

    public function testUsersCanBeDeactivatedWithNumber()
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

    public function testUsersCanBeDeactivatedWithBooleanFalse()
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

    public function testUsersUpdatingThemselvesDoNotDeactivateTheirAccount()
    {
        $admin = User::factory()->editUsers()->create(['activated' => true]);

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'first_name' => $admin->first_name,
                'username' => $admin->username,
            ]);

        $this->assertEquals(1, $admin->refresh()->activated);
    }

    public function testEditingUsersCannotEditEscalationFieldsForAdmins()
    {
        $editing_user = User::factory()->editUsers()->create(['activated' => true]);
        $hashed_original = Hash::make('my-awesome-password!!!!!12345');
        $admin = User::factory()->admin()->create(['username' => 'TestAdminUser', 'email'=> 'admin@example.org', 'password' => $hashed_original, 'activated' => true]);

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

    public function testAdminUsersCannotEditFieldsForSuperAdmins()
    {
        $admin = User::factory()->admin()->create(['activated' => true]);
        $hashed_original = Hash::make('my-awesome-password!!!!!12345');
        $superuser = User::factory()->superuser()->create(['username' => 'TestSuperUser', 'email'=> 'superuser@example.org', 'password' => $hashed_original, 'activated' => true]);

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


    public function testMultiCompanyUserCannotBeMovedIfHasAssetInDifferentCompany()
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
            'first_name'      => 'test',
            'username'        => 'test',
            'company_id'      => $companyB->id,
            'redirect_option' => 'index'
        ])->assertRedirect(route('users.index'));

        $asset->checkOut($user, $superUser);

        // asset assigned, therefore error
        $response = $this->actingAs($superUser)->patchJson(route('users.update', $user), [
            'first_name'      => 'test',
            'username'        => 'test',
            'company_id'      => $companyB->id,
            'redirect_option' => 'index'
        ]);

        $this->followRedirects($response)->assertSee('error');
    }

    public function testMultiCompanyUserCanBeUpdatedIfHasAssetInSameCompany()
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
            'first_name'      => 'test',
            'username'        => 'test',
            'company_id'      => $companyA->id,
            'redirect_option' => 'index'
        ])->assertRedirect(route('users.index'));

        $asset->checkOut($user, $superUser);

        // asset assigned, therefore error
        $response = $this->actingAs($superUser)->patchJson(route('users.update', $user), [
            'first_name'      => 'test',
            'username'        => 'test',
            'company_id'      => $companyA->id,
            'redirect_option' => 'index'
        ]);

        $this->followRedirects($response)->assertSee('success');
    }

    /**
     * This can occur if the user edit screen is open in one tab and
     * the user is deleted in another before the edit form is submitted.
     * @link https://app.shortcut.com/grokability/story/29166
     */
    public function testAttemptingToUpdateDeletedUserIsHandledGracefully()
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
