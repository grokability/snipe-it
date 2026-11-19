<?php

use App\Models\Asset;
use App\Models\MaintenanceType;
use App\Models\User;

test('checkout snapshot is captured when asset is checked out', function () {
    $actor = User::factory()->superuser()->create();
    $assignedUser = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignedUser)->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Snapshot Test',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2026-01-01',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'checked_out_to_id' => $assignedUser->id,
        'checked_out_to_type' => User::class,
    ]);
});

test('checkout snapshot is null when asset is not checked out', function () {
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'No Checkout Test',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2026-01-01',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'checked_out_to_id' => null,
        'checked_out_to_type' => null,
    ]);
});

test('responsible party omitted persists as null', function () {
    // Old behavior: the controller filled a missing / empty
    // responsible_party_id with the acting user via `?: auth()->id()`.
    // That silently defeated a deliberate clear on the create form
    // (issue #19452), so the controller now stores whatever the
    // form submits verbatim. The form template pre-selects the
    // acting user as a UX default so most creates still end up
    // with the acting user — but only when the user leaves the
    // pre-selection alone.
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'RP Omit Test',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2026-01-01',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'responsible_party_id' => null,
    ]);
});

test('responsible party can be set to another user', function () {
    $actor = User::factory()->superuser()->create();
    $technician = User::factory()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'RP Explicit Test',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'responsible_party_id' => $technician->id,
            'start_date' => '2026-01-01',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'responsible_party_id' => $technician->id,
    ]);
});

test('maintenance type id syncs legacy asset maintenance type', function () {
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create(['name' => 'Custom Calibration']);

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Type Sync Test',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2026-01-01',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'maintenance_type_id' => $type->id,
        'asset_maintenance_type' => 'Custom Calibration',
    ]);
});
