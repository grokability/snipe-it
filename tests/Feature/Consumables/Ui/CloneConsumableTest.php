<?php

use App\Models\Consumable;
use App\Models\Supplier;
use App\Models\User;

test('permission required to clone consumable', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('consumables.clone.create', $consumable))
        ->assertForbidden();
});

test('create permission alone is not enough to clone consumable', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->createConsumables()->create())
        ->get(route('consumables.clone.create', $consumable))
        ->assertForbidden();
});

test('clone page renders', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->cloneConsumables()->create())
        ->get(route('consumables.clone.create', $consumable))
        ->assertOk();
});

test('clone prefills supplier and order context from last order', function () {
    $supplier = Supplier::factory()->create();
    $source = Consumable::factory()
        ->withInitialAcquisition($supplier, 5.75, '2026-05-10')
        ->create(['qty' => 20]);

    $source->orderItems()->latest('id')->first()->order()->update([
        'order_number' => 'PO-CONS-CLONE-1',
    ]);

    $response = $this->actingAs(User::factory()->cloneConsumables()->create())
        ->get(route('consumables.clone.create', $source))
        ->assertOk();

    $response->assertSee('name="supplier_id"', false);
    $response->assertSee('value="'.$supplier->id.'"', false);
    $response->assertSee('name="order_number"', false);
    $response->assertSee('value="PO-CONS-CLONE-1"', false);
    $response->assertSee('name="purchase_date"', false);
    $response->assertSee('value="2026-05-10"', false);
    $response->assertSee('name="purchase_cost"', false);
    $response->assertSee('value="5.75"', false);
});
