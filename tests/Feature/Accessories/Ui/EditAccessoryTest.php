<?php

use App\Models\Accessory;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('accessories.edit', Accessory::factory()->create()->id))
        ->assertOk();
});

test('edit page ships requestable checkbox and reflects current value', function () {
    // Regression guard for the migration off @include('partials.forms
    // .edit.requestable') to <x-form.checkbox-row>. The requestable
    // checkbox must still emit name="requestable" and reflect the
    // stored value as checked.
    $requestable = Accessory::factory()->create(['requestable' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('accessories.edit', $requestable))
        ->assertOk()
        ->assertSee('name="requestable"', false)
        ->assertSee('checked', false);
});
