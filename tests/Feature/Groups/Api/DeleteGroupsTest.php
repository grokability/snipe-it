<?php

namespace Tests\Feature\Groups\Api;

use App\Models\Group;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteGroupsTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $group = Group::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.groups.destroy', $group))
            ->assertForbidden();

        $this->assertDatabaseHas('permission_groups', ['id' => $group->id]);
    }

    public function testCanDeleteGroup()
    {
        $group = Group::factory()->create();

        // only super admins can delete groups
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.groups.destroy', $group))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
    }
}
