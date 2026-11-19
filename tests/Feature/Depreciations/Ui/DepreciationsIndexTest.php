<?php

use App\Models\User;

test('permission required to view depreciations list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('depreciations.index'))
        ->assertForbidden();
});

test('user can list depreciations', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('depreciations.index'))
        ->assertOk();
});
