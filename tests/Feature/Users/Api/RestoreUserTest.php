<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class RestoreUserTest extends TestCase
{


    public function testErrorReturnedViaApiIfUserDoesNotExist()
    {
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->postJson(route('api.users.restore', 'invalid-id'))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function testErrorReturnedViaApiIfUserIsNotDeleted()
    {
        $user = User::factory()->create();
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->postJson(route('api.users.restore', $user->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }


    public function testDeniedPermissionsForRestoringUserViaApi()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.users.restore', User::factory()->deletedUser()->create()))
            ->assertStatus(403)
            ->json();
    }

    public function testSuccessPermissionsForRestoringUserViaApi()
    {
        $deleted_user = User::factory()->deletedUser()->create();

        $this->actingAsForApi(User::factory()->admin()->create())
            ->postJson(route('api.users.restore', ['user' => $deleted_user]))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        $deleted_user->refresh();
        $this->assertNull($deleted_user->deleted_at);
    }

    public function testPermissionsForRestoringIfNotInSameCompanyAndNotSuperadmin()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $userFromA = User::factory()->deletedUser()->deleteUsers()->for($companyA)->create();
        $userFromB = User::factory()->deletedUser()->deleteUsers()->for($companyB)->create();

        $this->actingAsForApi($userFromA)
            ->postJson(route('api.users.restore', ['user' => $userFromB->id]))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        $userFromB->refresh();
        $this->assertNotNull($userFromB->deleted_at);

        $this->actingAsForApi($userFromB)
            ->postJson(route('api.users.restore', ['user' => $userFromA->id]))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        $userFromA->refresh();
        $this->assertNotNull($userFromA->deleted_at);

        $this->actingAsForApi($superuser)
            ->postJson(route('api.users.restore', ['user' => $userFromA->id]))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('success')
            ->json();

        $userFromA->refresh();
        $this->assertNull($userFromA->deleted_at);

    }




}
