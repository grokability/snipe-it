<?php

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;

test('deletable component reports edit and delete selectable', function () {
    $component = Component::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index'))
        ->assertOk()
        ->assertJsonFragment([
            'id' => $component->id,
        ])
        ->assertJsonPath('rows.0.available_actions.bulk_selectable.edit', true)
        ->assertJsonPath('rows.0.available_actions.bulk_selectable.delete', true);
});

test('component with active checkouts reports delete unselectable', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $component->assets()->attach(Asset::factory()->create()->id, [
        'assigned_qty' => 2,
        'created_at' => now(),
        'created_by' => 1,
    ]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index'))
        ->assertOk();

    $row = collect($response->json('rows'))->firstWhere('id', $component->id);
    expect($row)->not->toBeNull();
    expect($row['available_actions']['bulk_selectable']['edit'])->toBeTrue();
    expect($row['available_actions']['bulk_selectable']['delete'])->toBeFalse();
});
