<?php

namespace Tests\Unit\Actions\Permissions;

use App\Actions\Permissions\PreserveUnauthorizedPrivilegedPermissionsAction;
use App\Models\User;
use Tests\TestCase;

class PreserveUnauthorizedPrivilegedPermissionsActionTest extends TestCase
{
    public function test_superuser_can_modify_privileged_keys(): void
    {
        $actor = User::factory()->superuser()->create();

        $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
            requestedPermissions: ['admin' => '0', 'superuser' => '0', 'users.view' => '1'],
            authenticatedUser: $actor,
            originalPermissions: ['admin' => '1', 'superuser' => '1']
        );

        $this->assertSame('0', (string) $normalized['admin']);
        $this->assertSame('0', (string) $normalized['superuser']);
        $this->assertSame('1', (string) $normalized['users.view']);
    }

    public function test_admin_cannot_change_existing_superuser_key(): void
    {
        $actor = User::factory()->admin()->create();

        $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
            requestedPermissions: ['admin' => '0', 'superuser' => '0', 'users.view' => '1'],
            authenticatedUser: $actor,
            originalPermissions: ['admin' => '1', 'superuser' => '1']
        );

        $this->assertSame('0', (string) $normalized['admin']);
        $this->assertSame('1', (string) $normalized['superuser']);
        $this->assertSame('1', (string) $normalized['users.view']);
    }

    public function test_admin_cannot_add_superuser_key_when_original_is_missing(): void
    {
        $actor = User::factory()->admin()->create();

        $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
            requestedPermissions: ['admin' => '1', 'superuser' => '1', 'users.view' => '1'],
            authenticatedUser: $actor,
            originalPermissions: ['admin' => '0']
        );

        $this->assertArrayNotHasKey('superuser', $normalized);
        $this->assertSame('1', (string) $normalized['admin']);
        $this->assertSame('1', (string) $normalized['users.view']);
    }

    public function test_non_admin_cannot_change_existing_admin_or_superuser_keys(): void
    {
        $actor = User::factory()->editUsers()->create();

        $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
            requestedPermissions: ['admin' => '1', 'superuser' => '1', 'users.view' => '1'],
            authenticatedUser: $actor,
            originalPermissions: ['admin' => '0', 'superuser' => '0']
        );

        $this->assertSame('0', (string) $normalized['admin']);
        $this->assertSame('0', (string) $normalized['superuser']);
        $this->assertSame('1', (string) $normalized['users.view']);
    }

    public function test_non_admin_cannot_add_missing_admin_or_superuser_keys(): void
    {
        $actor = User::factory()->editUsers()->create();

        $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
            requestedPermissions: ['admin' => '1', 'superuser' => '1', 'users.view' => '1'],
            authenticatedUser: $actor,
            originalPermissions: []
        );

        $this->assertArrayNotHasKey('admin', $normalized);
        $this->assertArrayNotHasKey('superuser', $normalized);
        $this->assertSame('1', (string) $normalized['users.view']);
    }
}
