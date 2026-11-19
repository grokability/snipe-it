<?php

use App\Models\Manufacturer;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('manufacturers.show', Manufacturer::factory()->create()))
        ->assertOk();
});
