<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteUsersTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertForbidden();

        $this->assertNotSoftDeleted($user);
    }

    public function testErrorReturnedViaApiIfUserDoesNotExist()
    {
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', 'invalid-id'))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function testErrorReturnedViaApiIfUserIsAlreadyDeleted()
    {
        $user = User::factory()->deletedUser()->create();
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function testDisallowUserDeletionViaApiIfStillManagingPeople()
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

    public function testDisallowUserDeletionViaApiIfStillManagingLocations()
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

    public function testDisallowUserDeletionViaApiIfStillHasLicenses()
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

    public function testUsersCannotDeleteThemselves()
    {
        $user = User::factory()->deleteUsers()->create();
        $this->actingAsForApi($user)
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $userFromA = User::factory()->deleteUsers()->for($companyA)->create();
        $userFromB = User::factory()->deleteUsers()->for($companyB)->create();

        $this->actingAsForApi($userFromA)
            ->deleteJson(route('api.users.destroy',  $userFromB))
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

    public function testCanDeleteUser()
    {
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->deleteJson(route('api.users.destroy', $user))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($user);
    }
}
