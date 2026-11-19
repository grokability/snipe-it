<?php

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\User;

test('index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.maintenance-types.index'))
        ->assertForbidden();
});

test('can list maintenance types', function () {
    MaintenanceType::factory()->count(3)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.maintenance-types.index'))
        ->assertOk()
        ->assertJsonStructure(['total', 'rows']);
});

test('can show maintenance type', function () {
    $type = MaintenanceType::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.maintenance-types.show', $type))
        ->assertOk()
        ->assertJsonFragment(['name' => $type->name]);
});

test('can create maintenance type', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.maintenance-types.store'), ['name' => 'My Custom Type'])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('maintenance_types', ['name' => 'My Custom Type']);
});

test('create requires name', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.maintenance-types.store'), [])
        ->assertStatusMessageIs('error');
});

test('can update maintenance type', function () {
    $type = MaintenanceType::factory()->create(['name' => 'Old Name']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->putJson(route('api.maintenance-types.update', $type), ['name' => 'New Name'])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('maintenance_types', ['id' => $type->id, 'name' => 'New Name']);
});

test('can delete maintenance type', function () {
    $type = MaintenanceType::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->deleteJson(route('api.maintenance-types.destroy', $type))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($type);
});

test('cannot delete maintenance type that is in use', function () {
    $type = MaintenanceType::factory()->create();
    Maintenance::factory()->create([
        'maintenance_type_id' => $type->id,
        'item_id' => Asset::factory()->create()->id,
        'item_type' => Asset::class,
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->deleteJson(route('api.maintenance-types.destroy', $type))
        ->assertStatus(409)
        ->assertStatusMessageIs('error');

    $this->assertDatabaseHas('maintenance_types', ['id' => $type->id, 'deleted_at' => null]);
});

test('index exposes maintenances count', function () {
    $type = MaintenanceType::factory()->create();
    Maintenance::factory()->count(2)->create([
        'maintenance_type_id' => $type->id,
        'item_id' => Asset::factory()->create()->id,
        'item_type' => Asset::class,
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.maintenance-types.index'))
        ->assertOk()
        ->assertJsonFragment(['id' => $type->id, 'maintenances_count' => 2]);
});
