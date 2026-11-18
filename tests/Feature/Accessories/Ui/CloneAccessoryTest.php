<?php

use App\Models\Accessory;
use App\Models\Supplier;
use App\Models\User;

test('permission required to clone accessory', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('clone/accessories', $accessory))
        ->assertForbidden();
});

test('create permission alone is not enough to clone accessory', function () {
    // Cloning renders the source accessory's data into the create form,
    // so a user with only accessories.create (no accessories.view)
    // must be blocked.
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->createAccessories()->create())
        ->get(route('clone/accessories', $accessory))
        ->assertForbidden();
});

test('clone page renders', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->cloneAccessories()->create())
        ->get(route('clone/accessories', $accessory))
        ->assertOk();
});

test('clone prefills supplier and order context from last order', function () {
    $supplier = Supplier::factory()->create();
    $source = Accessory::factory()
        ->withInitialAcquisition($supplier, 42.50, '2026-04-15')
        ->create(['qty' => 10]);

    $source->orderItems()->latest('id')->first()->order()->update([
        'order_number' => 'PO-CLONE-PREFILL-42',
    ]);

    $response = $this->actingAs(User::factory()->cloneAccessories()->create())
        ->get(route('clone/accessories', $source))
        ->assertOk();

    $response->assertSee('name="supplier_id"', false);
    $response->assertSee('value="'.$supplier->id.'"', false);
    $response->assertSee('name="order_number"', false);
    $response->assertSee('value="PO-CLONE-PREFILL-42"', false);
    $response->assertSee('name="purchase_date"', false);
    $response->assertSee('value="2026-04-15"', false);
    $response->assertSee('name="purchase_cost"', false);
    $response->assertSee('value="42.50"', false);
});

test('clone leaves prefill fields blank when source has no order history', function () {
    $source = Accessory::factory()->create(['qty' => 0]);

    expect($source->orderItems()->latest('id')->first())->toBeNull('Expected qty=0 accessory to have no observer-written order (baseline for the no-history case).');

    $response = $this->actingAs(User::factory()->cloneAccessories()->create())
        ->get(route('clone/accessories', $source))
        ->assertOk();

    $response->assertSee('name="order_number"', false);
    $response->assertDontSee('value="PO-', false);
});
