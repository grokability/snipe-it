<?php

namespace Tests\Feature\Middleware;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_denied_browser_request_renders_the_existing_forbidden_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.security.index'))
            ->assertForbidden()
            ->assertSee('403 Forbidden.')
            ->assertSee('img/sad-panda.png');
    }

    public function test_denied_settings_api_request_returns_json(): void
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.settings.login_attempts'))
            ->assertForbidden()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }

    public function test_denied_scim_request_returns_json(): void
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson('/scim/v2/Users')
            ->assertForbidden()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }

    public function test_superuser_can_access_settings(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.security.index'))
            ->assertOk();
    }

    public function test_superuser_can_access_the_settings_api(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.settings.login_attempts'))
            ->assertOk();
    }

    public function test_admin_without_superuser_cannot_access_privileged_routes(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('settings.security.index'))
            ->assertForbidden();

        $this->actingAsForApi($user)
            ->getJson(route('api.settings.login_attempts'))
            ->assertForbidden();
        $this->getJson('/scim/v2/Users')->assertForbidden();
    }

    public function test_group_granted_superuser_can_access_privileged_routes(): void
    {
        $group = Group::factory()->create(['permissions' => json_encode(['superuser' => '1'])]);
        $user = User::factory()->create();
        $user->groups()->attach($group);

        $this->actingAs($user)
            ->get(route('settings.security.index'))
            ->assertOk();

        $this->actingAsForApi($user)
            ->getJson(route('api.settings.login_attempts'))
            ->assertOk();
        $this->getJson('/scim/v2/Users')->assertOk();
    }

    public function test_superadmin_permission_key_does_not_grant_superuser_access(): void
    {
        $user = User::factory()->create(['permissions' => json_encode(['superadmin' => '1'])]);

        $this->actingAs($user)
            ->get(route('settings.security.index'))
            ->assertForbidden();

        $this->actingAsForApi($user)
            ->getJson('/scim/v2/Users')
            ->assertForbidden();
    }
}
