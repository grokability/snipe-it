<?php

namespace Tests\Feature\Users\Ui;

use Tests\TestCase;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Accessory;
use App\Models\User;
use App\Models\Company;

use App\Models\Asset;

class DeleteUserTest extends TestCase
{

    public function testUserCanDeleteAnotherUser()
    {
        $user = User::factory()->deleteUsers()->viewUsers()->create();
        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertTrue($user->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', ['user' => $user->id]))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee(trans('general.notification_success'));
    }


    public function testErrorReturnedIfUserDoesNotExist()
    {
        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', ['user' => '40596803548609346']))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));
        $this->followRedirects($response)->assertSee(trans('alert-danger'));
    }

    public function testErrorReturnedIfUserIsAlreadyDeleted()
    {
        $user = User::factory()->deletedUser()->viewUsers()->create();
        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $user->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

          $this->followRedirects($response)->assertSee(trans('general.error'));
    }

    public function testCanViewSoftDeletedUser()
    {
        $user = User::factory()->deletedUser()->viewUsers()->create();
        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->get(route('users.show', $user->id))
            ->assertStatus(200);
    }


    public function testFmcsPermissionsToDeleteUser()
    {

        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $userFromA = User::factory()->deleteUsers()->for($companyA)->create();
        $userFromB = User::factory()->deleteUsers()->for($companyB)->create();

        $response =  $this->followingRedirects()->actingAs($userFromA)
            ->delete(route('users.destroy', ['user' => $userFromB->id]))
            ->assertStatus(403);
        $this->followRedirects($response)->assertSee('sad-panda.png');

        $userFromB->refresh();
        $this->assertNull($userFromB->deleted_at);


        $response = $this->actingAs($userFromB)
            ->delete(route('users.destroy', ['user' => $userFromA->id]))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));
        $this->followRedirects($response)->assertSee('sad-panda.png');

        $userFromA->refresh();
        $this->assertNull($userFromA->deleted_at);

        $response = $this->actingAs($superuser)
            ->delete(route('users.destroy', ['user' => $userFromA->id]))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));
        $this->followRedirects($response)->assertSee('Success');

        $userFromA->refresh();
        $this->assertNotNull($userFromA->deleted_at);

    }


    public function testDisallowUserDeletionIfStillManagingPeople()
    {
        $manager = User::factory()->create();
        User::factory()->count(1)->create(['manager_id' => $manager->id]);

        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertFalse($manager->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $manager->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Error');
    }

    public function testDisallowUserDeletionIfStillManagingLocations()
    {
        $manager = User::factory()->create();
        Location::factory()->count(2)->create(['manager_id' => $manager->id]);

        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertFalse($manager->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $manager->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

           $this->followRedirects($response)->assertSee('Error');
    }

    public function testDisallowUserDeletionIfStillHaveAccessories()
    {
        $user = User::factory()->create();
        Accessory::factory()->count(3)->checkedOutToUser($user)->create();

        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertFalse($user->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $user->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Error');
    }

    public function testDisallowUserDeletionIfStillHaveLicenses()
    {
        $user = User::factory()->create();
        LicenseSeat::factory()->count(4)->create(['assigned_to' => $user->id]);

        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertFalse($user->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $user->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Error');
    }


    public function testAllowUserDeletionIfNotManagingLocations()
    {
        $manager = User::factory()->create();
        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertTrue($manager->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $manager->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');

    }

    public function testDisallowUserDeletionIfNoDeletePermissions()
    {
        $manager = User::factory()->create();
        Location::factory()->create(['manager_id' => $manager->id]);
        $this->actingAs(User::factory()->editUsers()->viewUsers()->create())->assertFalse($manager->isDeletable());
    }

    public function testDisallowUserDeletionIfTheyStillHaveAssets()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset->id), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'name' => 'Changed Name',
            ]);

        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertFalse($user->isDeletable());

        $response = $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())
            ->delete(route('users.destroy', $user->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Error');
    }


    public function testUsersCannotDeleteThemselves()
    {
        $manager = User::factory()->deleteUsers()->viewUsers()->create();
        $this->actingAs(User::factory()->deleteUsers()->viewUsers()->create())->assertTrue($manager->isDeletable());

        $response = $this->actingAs($manager)
            ->delete(route('users.destroy', $manager->id))
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Error');
    }


}
