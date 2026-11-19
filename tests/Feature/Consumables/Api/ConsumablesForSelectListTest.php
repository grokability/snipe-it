<?php

use App\Models\Consumable;
use App\Models\User;

test('requires view selectlists permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.consumables.selectlist'))
        ->assertForbidden();
});

test('consumables are returned for select list', function () {
    [$consumableA, $consumableB] = Consumable::factory()->count(2)->create();

    $this->actingAsForApi(User::factory()->createConsumables()->create())
        ->getJson(route('api.consumables.selectlist'))
        ->assertOk()
        ->assertResponseContainsInResults($consumableA)
        ->assertResponseContainsInResults($consumableB);
});
