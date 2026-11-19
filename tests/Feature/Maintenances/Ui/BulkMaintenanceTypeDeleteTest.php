<?php

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\User;

test('bulk delete requires permission', function () {
    $type = MaintenanceType::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance-types.bulk.delete'), ['ids' => [$type->id]])
        ->assertForbidden();

    $this->assertDatabaseHas('maintenance_types', ['id' => $type->id, 'deleted_at' => null]);
});

test('bulk delete removes selected unused types', function () {
    $doomed = MaintenanceType::factory()->count(3)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenance-types.bulk.delete'), [
            'ids' => $doomed->pluck('id')->all(),
        ])
        ->assertRedirect(route('maintenance-types.index'))
        ->assertSessionHas('success')
        ->assertSessionMissing('multi_error_messages');

    $doomed->each(fn ($t) => $this->assertSoftDeleted('maintenance_types', ['id' => $t->id]));
});

test('bulk delete skips types still in use', function () {
    $inUse = MaintenanceType::factory()->create();
    Maintenance::factory()->create([
        'maintenance_type_id' => $inUse->id,
        'item_id' => Asset::factory()->create()->id,
        'item_type' => Asset::class,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenance-types.bulk.delete'), [
            'ids' => [$inUse->id],
        ])
        ->assertRedirect(route('maintenance-types.index'))
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    $this->assertDatabaseHas('maintenance_types', ['id' => $inUse->id, 'deleted_at' => null]);
});

test('bulk delete handles mixed batch', function () {
    $unused = MaintenanceType::factory()->count(2)->create();
    $inUse = MaintenanceType::factory()->create();
    Maintenance::factory()->create([
        'maintenance_type_id' => $inUse->id,
        'item_id' => Asset::factory()->create()->id,
        'item_type' => Asset::class,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenance-types.bulk.delete'), [
            'ids' => array_merge($unused->pluck('id')->all(), [$inUse->id]),
        ])
        ->assertRedirect(route('maintenance-types.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('multi_error_messages');

    $unused->each(fn ($t) => $this->assertSoftDeleted('maintenance_types', ['id' => $t->id]));
    $this->assertDatabaseHas('maintenance_types', ['id' => $inUse->id, 'deleted_at' => null]);
});

test('bulk delete skips missing ids without crashing', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenance-types.bulk.delete'), [
            'ids' => [999999],
        ])
        ->assertRedirect(route('maintenance-types.index'))
        ->assertSessionHas('multi_error_messages');
});
