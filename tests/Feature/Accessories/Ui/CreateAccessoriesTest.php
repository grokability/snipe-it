<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires permission to view create accessory page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('accessories.create'))
        ->assertForbidden();
});

test('create accessory page renders', function () {
    $this->actingAs(User::factory()->createAccessories()->create())
        ->get(route('accessories.create'))
        ->assertOk()
        ->assertViewIs('accessories.edit');
});

test('requires permission to create accessory', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('accessories.store'))
        ->assertForbidden();
});

test('valid data required to create accessory', function () {
    $this->actingAs(User::factory()->createAccessories()->create())
        ->post(route('accessories.store'), [
            //
        ])
        ->assertSessionHasErrors([
            'name',
            'category_id',
        ]);
});

test('can create accessory', function () {
    $category = Category::factory()->create();
    $company = Company::factory()->create();
    $location = Location::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $supplier = Supplier::factory()->create();

    // order_number, supplier_id, purchase_date, purchase_cost are
    // valid inputs on create — they flow to the observer-written
    // Order + OrderItem. supplier_id ALSO seeds the parent's
    // default_supplier_id template for future orders.
    $data = [
        'category_id' => $category->id,
        'company_id' => $company->id,
        'location_id' => $location->id,
        'manufacturer_id' => $manufacturer->id,
        'min_amt' => '1',
        'model_number' => '12345',
        'name' => 'My Accessory Name',
        'notes' => 'Some notes here',
        'purchase_cost' => '99.98',
        'purchase_date' => '2024-09-04',
        'qty' => '3',
        'supplier_id' => $supplier->id,
    ];

    $user = User::factory()->createAccessories()->create();

    $this->actingAs($user)
        ->post(route('accessories.store'), array_merge($data, ['redirect_option' => 'index']))
        ->assertRedirect(route('accessories.index'));

    $this->assertDatabaseHas('accessories', [
        'category_id' => $category->id,
        'company_id' => $company->id,
        'location_id' => $location->id,
        'manufacturer_id' => $manufacturer->id,
        'min_amt' => '1',
        'model_number' => '12345',
        'name' => 'My Accessory Name',
        'notes' => 'Some notes here',
        'qty' => '3',
        'default_supplier_id' => $supplier->id,
        'created_by' => $user->id,
    ]);
});
