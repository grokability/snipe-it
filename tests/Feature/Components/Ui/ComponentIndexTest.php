<?php

use App\Models\User;

test('permission required to view components list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('components.index'))
        ->assertForbidden();
});

test('user can list components', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.index'))
        ->assertOk();
});
