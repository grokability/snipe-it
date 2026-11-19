<?php

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\User;

test('show page renders', function () {
    $type = MaintenanceType::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenance-types.show', $type))
        ->assertOk()
        ->assertSeeText($type->name);
});

test('show requires permission', function () {
    $type = MaintenanceType::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance-types.show', $type))
        ->assertForbidden();
});

test('can delete unused maintenance type', function () {
    $type = MaintenanceType::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->delete(route('maintenance-types.destroy', $type))
        ->assertRedirect(route('maintenance-types.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($type);
});

test('cannot delete maintenance type that is in use', function () {
    $type = MaintenanceType::factory()->create();
    Maintenance::factory()->create([
        'maintenance_type_id' => $type->id,
        'item_id' => Asset::factory()->create()->id,
        'item_type' => Asset::class,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->delete(route('maintenance-types.destroy', $type))
        ->assertRedirect(route('maintenance-types.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('maintenance_types', ['id' => $type->id, 'deleted_at' => null]);
});
