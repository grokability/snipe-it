<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('consumables.create'))
        ->assertForbidden();
});

test('can render create consumable page', function () {
    $this->actingAs(User::factory()->createConsumables()->create())
        ->get(route('consumables.create'))
        ->assertOk()
        ->assertViewIs('consumables.edit');
});

test('can create consumable', function () {
    // supplier_id / purchase_date / purchase_cost / order_number in
    // the request body flow to the observer-written Order + OrderItem
    // (see ConsumableObserverOrderTest). The parent's
    // default_supplier_id gets seeded from supplier_id so future
    // orders pre-populate correctly.
    $supplier = Supplier::factory()->create();
    $data = [
        'company_id' => Company::factory()->create()->id,
        'name' => 'My Consumable',
        'category_id' => Category::factory()->consumableInkCategory()->create()->id,
        'supplier_id' => $supplier->id,
        'manufacturer_id' => Manufacturer::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
        'model_number' => '1234',
        'item_no' => '5678',
        'purchase_date' => '2024-12-05',
        'purchase_cost' => '89.45',
        'qty' => '10',
        'min_amt' => '1',
        'notes' => 'Some Notes',
    ];

    $this->actingAs(User::factory()->createConsumables()->create())
        ->post(route('consumables.store'), $data + [
            'redirect_option' => 'index',
            'category_type' => 'consumable',
        ])
        ->assertRedirect(route('consumables.index'));

    $this->assertDatabaseHas('consumables', [
        'company_id' => $data['company_id'],
        'name' => 'My Consumable',
        'category_id' => $data['category_id'],
        'manufacturer_id' => $data['manufacturer_id'],
        'location_id' => $data['location_id'],
        'model_number' => '1234',
        'item_no' => '5678',
        'qty' => '10',
        'min_amt' => '1',
        'notes' => 'Some Notes',
        'default_supplier_id' => $supplier->id,
    ]);
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.create'))
        ->assertOk();
});
