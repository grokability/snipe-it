<?php

use App\Models\Manufacturer;
use App\Models\User;

test('permission required to store manufacturer', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('manufacturers.store'), [
            'name' => 'Test Manufacturer',
        ])
        ->assertStatus(403)
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('manufacturers.edit', Manufacturer::factory()->create()->id))
        ->assertOk();
});

test('user can edit manufacturers', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);
    expect(Manufacturer::where('name', 'Test Manufacturer')->exists())->toBeTrue();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->put(route('manufacturers.update', ['manufacturer' => $manufacturer]), [
            'name' => 'Test Manufacturer Edited',
            'notes' => 'Test Note Edited',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('manufacturers.index'));

    $this->followRedirects($response)->assertSee('Success');
    expect(Manufacturer::where('name', 'Test Manufacturer Edited')->where('notes', 'Test Note Edited')->exists())->toBeTrue();
});
