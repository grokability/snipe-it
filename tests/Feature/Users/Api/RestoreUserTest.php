<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class RestoreUserTest extends TestCase
{
    public function test_error_returned_via_api_if_user_does_not_exist()
    {
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->postJson(route('api.users.restore', 'invalid-id'))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_error_returned_via_api_if_user_is_not_deleted()
    {
        $user = User::factory()->create();
        $this->actingAsForApi(User::factory()->deleteUsers()->create())
            ->postJson(route('api.users.restore', $user->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_denied_permissions_for_restoring_user_via_api()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.users.restore', User::factory()->deletedUser()->create()))
            ->assertStatus(403)
            ->json();
    }

    public function test_success_permissions_for_restoring_user_via_api()
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

    public function test_permissions_for_restoring_if_not_in_same_company_and_not_superadmin()
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
