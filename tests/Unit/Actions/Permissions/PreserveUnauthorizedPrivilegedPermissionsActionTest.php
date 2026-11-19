<?php

use App\Actions\Permissions\PreserveUnauthorizedPrivilegedPermissionsAction;
use App\Models\User;

test('superuser can modify privileged keys', function () {
    $actor = User::factory()->superuser()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['admin' => '0', 'superuser' => '0', 'users.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['admin' => '1', 'superuser' => '1']
    );

    expect((string) $normalized['admin'])->toBe('0');
    expect((string) $normalized['superuser'])->toBe('0');
    expect((string) $normalized['users.view'])->toBe('1');
});

test('admin cannot change existing superuser key', function () {
    $actor = User::factory()->admin()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['admin' => '0', 'superuser' => '0', 'users.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['admin' => '1', 'superuser' => '1']
    );

    expect((string) $normalized['admin'])->toBe('0');
    expect((string) $normalized['superuser'])->toBe('1');
    expect((string) $normalized['users.view'])->toBe('1');
});

test('admin cannot add superuser key when original is missing', function () {
    $actor = User::factory()->admin()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['admin' => '1', 'superuser' => '1', 'users.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['admin' => '0']
    );

    $this->assertArrayNotHasKey('superuser', $normalized);
    expect((string) $normalized['admin'])->toBe('1');
    expect((string) $normalized['users.view'])->toBe('1');
});

test('non admin cannot change existing admin or superuser keys', function () {
    $actor = User::factory()->editUsers()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['admin' => '1', 'superuser' => '1', 'users.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['admin' => '0', 'superuser' => '0']
    );

    expect((string) $normalized['admin'])->toBe('0');
    expect((string) $normalized['superuser'])->toBe('0');
    expect((string) $normalized['users.view'])->toBe('1');
});

test('non admin cannot add missing admin or superuser keys', function () {
    $actor = User::factory()->editUsers()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['admin' => '1', 'superuser' => '1', 'users.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: []
    );

    $this->assertArrayNotHasKey('admin', $normalized);
    $this->assertArrayNotHasKey('superuser', $normalized);
    expect((string) $normalized['users.view'])->toBe('1');
});

test('non admin cannot grant themselves additional permissions', function () {
    $actor = User::factory()->editUsers()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['users.edit' => '1', 'assets.view' => '1', 'reports.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['users.edit' => '1'],
        targetUser: $actor,
    );

    expect($normalized)->toBe(['users.edit' => '1']);
});

test('non admin cannot remove their own permissions', function () {
    $actor = User::factory()->editUsers()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: [],
        authenticatedUser: $actor,
        originalPermissions: ['users.edit' => '1'],
        targetUser: $actor,
    );

    expect($normalized)->toBe(['users.edit' => '1']);
});

test('admin cannot modify their own permissions', function () {
    $actor = User::factory()->admin()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['admin' => '1', 'assets.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['admin' => '1'],
        targetUser: $actor,
    );

    expect($normalized)->toBe(['admin' => '1']);
});

test('superuser can modify their own permissions', function () {
    $actor = User::factory()->superuser()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['superuser' => '1', 'assets.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: ['superuser' => '1'],
        targetUser: $actor,
    );

    expect((string) $normalized['assets.view'])->toBe('1');
});

test('non admin can modify permissions of a different user', function () {
    $actor = User::factory()->editUsers()->create();
    $target = User::factory()->create();

    $normalized = PreserveUnauthorizedPrivilegedPermissionsAction::run(
        requestedPermissions: ['assets.view' => '1'],
        authenticatedUser: $actor,
        originalPermissions: [],
        targetUser: $target,
    );

    expect((string) $normalized['assets.view'])->toBe('1');
});
