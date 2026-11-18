<?php

use App\Models\User;

test('permission required to view category list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('categories.index'))
        ->assertForbidden();
});

test('user can list categories', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('categories.index'))
        ->assertOk();
});
