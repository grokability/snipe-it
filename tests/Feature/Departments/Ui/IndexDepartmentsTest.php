<?php

use App\Models\User;

test('permission required to view departments list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('departments.index'))
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('departments.index'))
        ->assertOk();
});

test('user can list departments', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('departments.index'))
        ->assertOk();
});
