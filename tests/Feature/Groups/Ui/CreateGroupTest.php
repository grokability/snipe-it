<?php

use App\Models\Group;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('groups.create'))
        ->assertOk();
});

test('user can create group', function () {
    expect(Group::where('name', 'Test Group')->exists())->toBeFalse();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('groups.store'), [
            'name' => 'Test Group',
            'notes' => 'Test Note',
        ])
        ->assertRedirect(route('groups.index'));

    expect(Group::where('name', 'Test Group')->where('notes', 'Test Note')->exists())->toBeTrue();
});
