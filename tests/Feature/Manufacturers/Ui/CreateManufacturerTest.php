<?php

use App\Models\Manufacturer;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('manufacturers.create'))
        ->assertOk();
});

test('user can create manufacturer', function () {
    expect(Manufacturer::where('name', 'Test Manufacturer')->exists())->toBeFalse();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('manufacturers.store'), [
            'name' => 'Test Manufacturer',
            'notes' => 'Test Note',
        ])
        ->assertRedirect(route('manufacturers.index'));

    expect(Manufacturer::where('name', 'Test Manufacturer')->where('notes', 'Test Note')->exists())->toBeTrue();
});
