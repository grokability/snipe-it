<?php

use App\Models\User;

test('permission required to view asset model list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('models.index'))
        ->assertForbidden();
});

test('user can list asset models', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('models.index'))
        ->assertOk();
});
