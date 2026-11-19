<?php

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;

test('checkout create screen blocks archived component', function () {
    $component = Component::factory()->archived()->create(['qty' => 5]);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.checkout.show', ['componentID' => $component->id]))
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.component')]));
});

test('checkout store blocks archived component', function () {
    $component = Component::factory()->archived()->create(['qty' => 5]);
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.checkout.store', ['componentID' => $component->id]), [
            'asset_id' => $asset->id,
            'assigned_qty' => 1,
        ])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.component')]));

    // No pivot row created.
    expect($component->assets()->count())->toBe(0);
});

test('checkout create screen allows non archived component', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.checkout.show', ['componentID' => $component->id]))
        ->assertOk();
});
