<?php

use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('accessories.index'))
        ->assertForbidden();
});

test('renders accessories index page', function () {
    $this->actingAs(User::factory()->viewAccessories()->create())
        ->get(route('accessories.index'))
        ->assertOk()
        ->assertViewIs('accessories.index');
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('accessories.index'))
        ->assertOk();
});
