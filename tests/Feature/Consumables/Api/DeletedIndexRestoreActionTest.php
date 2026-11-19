<?php

use App\Models\Consumable;
use App\Models\User;

test('soft deleted consumable row offers restore action', function () {
    $consumable = Consumable::factory()->create();
    $consumable->delete();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.consumables.index', ['status' => 'Deleted']))
        ->assertOk();

    $row = collect($response->json('rows'))->firstWhere('id', $consumable->id);
    expect($row)->not->toBeNull('Soft-deleted consumable must appear on the Deleted index.');
    expect($row['available_actions']['restore'] ?? false)->toBeTrue();
});

test('live consumable row does not offer restore action', function () {
    $consumable = Consumable::factory()->create();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.consumables.index'))
        ->assertOk();

    $row = collect($response->json('rows'))->firstWhere('id', $consumable->id);
    expect($row)->not->toBeNull();
    expect($row['available_actions']['restore'] ?? true)->toBeFalse();
});
