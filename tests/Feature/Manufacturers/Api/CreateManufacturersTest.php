<?php

use App\Models\Manufacturer;
use App\Models\User;

test('requires permission to create manufacturer', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.manufacturers.store'))
        ->assertForbidden();
});

test('can create manufacturer', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.manufacturers.store'), [
            'name' => 'Test Manufacturer',
            'notes' => 'Test Note',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->json();

    expect(Manufacturer::where('name', 'Test Manufacturer')->where('notes', 'Test Note')->exists())->toBeTrue();

    $manufacturer = Manufacturer::find($response['payload']['id']);
    expect($manufacturer->name)->toEqual('Test Manufacturer');
    expect($manufacturer->notes)->toEqual('Test Note');
});
