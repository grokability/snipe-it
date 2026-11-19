<?php

use App\Models\Group;
use App\Models\User;

test('updating group requires super admin permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->putJson(route('api.groups.update', Group::factory()->create()))
        ->assertForbidden();
});

test('can update group with permissions array payload', function () {
    $group = Group::factory()->create([
        'permissions' => json_encode(['admin' => '0']),
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->putJson(route('api.groups.update', $group), [
            'name' => 'Updated Group Name',
            'notes' => 'Updated Group Notes',
            'permissions' => [
                'admin' => '1',
                'reports.view' => '0',
            ],
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $decoded = (array) $group->refresh()->decodePermissions();

    expect($group->name)->toBe('Updated Group Name');
    expect($group->notes)->toBe('Updated Group Notes');
    expect((string) ($decoded['admin'] ?? null))->toBe('1');
    expect((string) ($decoded['reports.view'] ?? null))->toBe('0');
});

test('can update group with permissions json string payload', function () {
    $group = Group::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.groups.update', $group), [
            'permissions' => '{"admin":"1","reports.view":"0","invalid.permission":"1"}',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $decoded = (array) $group->refresh()->decodePermissions();

    expect($decoded)->toHaveKey('admin');
    $this->assertArrayHasKey('reports.view', $decoded);
    $this->assertArrayNotHasKey('invalid.permission', $decoded);
});

test('permissions are preserved when not passed on update', function () {
    $group = Group::factory()->create([
        'permissions' => json_encode(['admin' => '1']),
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.groups.update', $group), [
            'name' => 'Rename Only',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $decoded = (array) $group->refresh()->decodePermissions();

    expect($group->name)->toBe('Rename Only');
    expect((string) ($decoded['admin'] ?? null))->toBe('1');
});
