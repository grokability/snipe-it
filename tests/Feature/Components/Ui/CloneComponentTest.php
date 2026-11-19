<?php

use App\Models\Component;
use App\Models\Supplier;
use App\Models\User;

test('permission required to create component', function () {
    $component = Component::factory()->create();
    $this->actingAs(User::factory()->create())
        ->get(route('components.clone.create', $component))
        ->assertForbidden();
});

test('create permission alone is not enough to clone component', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->createComponents()->create())
        ->get(route('components.clone.create', $component))
        ->assertForbidden();
});

test('page can be accessed', function () {
    $component = Component::factory()->create();
    $response = $this->actingAs(User::factory()->cloneComponents()->create())
        ->get(route('components.clone.create', $component));
    $response->assertStatus(200);
});

test('component can be cloned', function () {
    $component_to_clone = Component::factory()->create(['name' => 'Component to clone']);
    $this->actingAs(User::factory()->cloneComponents()->create())
        ->get(route('components.clone.create', $component_to_clone))
        ->assertOk()
        ->assertSee([
            'Component to clone',
        ], false);
});

test('clone prefills supplier and order context from last order', function () {
    $supplier = Supplier::factory()->create();
    $source = Component::factory()
        ->withInitialAcquisition($supplier, 12.34, '2026-04-01')
        ->create(['qty' => 5]);

    $source->orderItems()->latest('id')->first()->order()->update([
        'order_number' => 'PO-COMP-CLONE-9',
    ]);

    $response = $this->actingAs(User::factory()->cloneComponents()->create())
        ->get(route('components.clone.create', $source))
        ->assertOk();

    $response->assertSee('name="supplier_id"', false);
    $response->assertSee('value="'.$supplier->id.'"', false);
    $response->assertSee('name="order_number"', false);
    $response->assertSee('value="PO-COMP-CLONE-9"', false);
    $response->assertSee('name="purchase_date"', false);
    $response->assertSee('value="2026-04-01"', false);
    $response->assertSee('name="purchase_cost"', false);
    $response->assertSee('value="12.34"', false);
});
