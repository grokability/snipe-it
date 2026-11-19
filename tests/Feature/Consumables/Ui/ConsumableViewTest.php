<?php

use App\Models\Consumable;
use App\Models\User;

test('permission required to view consumable', function () {
    $consumable = Consumable::factory()->create();
    $this->actingAs(User::factory()->create())
        ->get(route('consumables.show', $consumable))
        ->assertForbidden();
});

test('user can view a consumable', function () {
    $consumable = Consumable::factory()->create();
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.show', $consumable))
        ->assertOk();
});
