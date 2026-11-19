<?php

use App\Models\Component;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.show', Component::factory()->create()))
        ->assertOk();
});
