<?php

use App\Models\Manufacturer;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('manufacturers.edit', Manufacturer::factory()->create()))
        ->assertOk();
});

test('user can edit manufacturers', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);
    expect(Manufacturer::where('name', 'Test Manufacturer')->exists())->toBeTrue();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->put(route('manufacturers.update', $manufacturer), [
            'name' => 'Test Manufacturer Edited',
            'notes' => 'Test Note Edited',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('manufacturers.index'));

    $this->followRedirects($response)->assertSee('Success');
    expect(Manufacturer::where('name', 'Test Manufacturer Edited')->where('notes', 'Test Note Edited')->exists())->toBeTrue();
});
