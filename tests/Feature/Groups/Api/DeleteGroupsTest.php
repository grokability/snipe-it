<?php

use App\Models\Group;
use App\Models\User;

test('requires permission', function () {
    $group = Group::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.groups.destroy', $group))
        ->assertForbidden();

    $this->assertDatabaseHas('permission_groups', ['id' => $group->id]);
});

test('can delete group', function () {
    $group = Group::factory()->create();

    // only super admins can delete groups
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->deleteJson(route('api.groups.destroy', $group))
        ->assertStatusMessageIs('success');

    $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
});
