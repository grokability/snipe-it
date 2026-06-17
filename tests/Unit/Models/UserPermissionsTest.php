<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    public function test_super_user_has_access_to_everything(): void
    {
        $super = User::factory()->superuser()->create();

        $this->assertTrue($super->hasAccess('admin'));
        $this->assertTrue($super->isSuperUser());
        $this->assertTrue($super->hasAccess('anything'));
    }

    public function test_user_with_granular_permission(): void
    {
        $user = User::factory()->create(['permissions' => json_encode(['assets.view' => '1'])]);

        $this->assertTrue($user->hasAccess('assets.view'));
        $this->assertFalse($user->hasAccess('assets.delete'));
        $this->assertFalse($user->isSuperUser());
    }

    public function test_decode_permissions_returns_array(): void
    {
        $user = User::factory()->create(['permissions' => json_encode(['assets.view' => '1', 'users.view' => '1'])]);

        $decoded = $user->decodePermissions();

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('assets.view', $decoded);
    }

    public function test_has_individual_permissions(): void
    {
        $withPerms = User::factory()->create(['permissions' => json_encode(['assets.view' => '1'])]);
        $withoutPerms = User::factory()->create(['permissions' => json_encode([])]);

        $this->assertTrue($withPerms->hasIndividualPermissions());
        $this->assertFalse($withoutPerms->hasIndividualPermissions());
    }

    public function test_is_admin_bool(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertIsBool($admin->isAdmin());
    }
}
