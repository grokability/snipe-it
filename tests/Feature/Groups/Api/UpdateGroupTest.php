<?php

namespace Tests\Feature\Groups\Api;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;

class UpdateGroupTest extends TestCase
{
    public function test_updating_group_requires_super_admin_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->putJson(route('api.groups.update', Group::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_update_group_with_permissions_array_payload()
    {
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

        $this->assertSame('Updated Group Name', $group->name);
        $this->assertSame('Updated Group Notes', $group->notes);
        $this->assertSame('1', (string) ($decoded['admin'] ?? null));
        $this->assertSame('0', (string) ($decoded['reports.view'] ?? null));
    }

    public function test_can_update_group_with_permissions_json_string_payload()
    {
        $group = Group::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.groups.update', $group), [
                'permissions' => '{"admin":"1","reports.view":"0","invalid.permission":"1"}',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $decoded = (array) $group->refresh()->decodePermissions();

        $this->assertArrayHasKey('admin', $decoded);
        $this->assertArrayHasKey('reports.view', $decoded);
        $this->assertArrayNotHasKey('invalid.permission', $decoded);
    }

    public function test_permissions_are_preserved_when_not_passed_on_update()
    {
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

        $this->assertSame('Rename Only', $group->name);
        $this->assertSame('1', (string) ($decoded['admin'] ?? null));
    }
}
