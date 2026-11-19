<?php

use App\Models\Component;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.edit', Component::factory()->create()))
        ->assertOk();
});
