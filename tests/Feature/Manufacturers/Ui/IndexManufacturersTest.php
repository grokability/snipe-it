<?php

use App\Models\Manufacturer;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('manufacturers.index'))
        ->assertOk();
});

test('cannot seed if manufacturers exist', function () {
    Manufacturer::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('manufacturers.seed'))
        ->assertStatus(302)
        ->assertSessionHas('error')
        ->assertRedirect(route('manufacturers.index'));
});
