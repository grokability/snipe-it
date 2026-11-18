<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\User;

test('clean accessory is bulk selectable', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.accessories.show', $accessory))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('checked out accessory is not bulk selectable', function () {
    $accessory = Accessory::factory()->create();
    AccessoryCheckout::factory()->create(['accessory_id' => $accessory->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.accessories.show', $accessory))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});
