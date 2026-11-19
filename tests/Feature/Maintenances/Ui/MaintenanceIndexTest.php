<?php

use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenances.index'))
        ->assertOk();
});

test('page renders with array shaped query param probes', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('/maintenances?completed[$ptt]=true&upcoming_status[$ptt]=overdue')
        ->assertOk();
});
