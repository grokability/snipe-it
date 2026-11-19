<?php

use App\Models\User;

test('permission required to view consumables list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('consumables.index'))
        ->assertForbidden();
});

test('user can list consumables', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.index'))
        ->assertOk();
});
