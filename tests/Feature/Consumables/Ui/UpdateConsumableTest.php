<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires permission to see edit consumable page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('consumables.edit', Consumable::factory()->create()))
        ->assertForbidden();
});

test('does not show edit consumable page from another company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $consumableForCompanyA = Consumable::factory()->for($companyA)->create();
    $userForCompanyB = User::factory()->editConsumables()->forCompany($companyB)->create();

    $this->actingAs($userForCompanyB)
        ->get(route('consumables.edit', $consumableForCompanyA))
        ->assertRedirect(route('consumables.index'));
});

test('edit consumable page renders', function () {
    $this->actingAs(User::factory()->editConsumables()->create())
        ->get(route('consumables.edit', Consumable::factory()->create()))
        ->assertOk()
        ->assertViewIs('consumables.edit');
});

test('cannot update consumable belonging to another company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $consumableForCompanyA = Consumable::factory()->for($companyA)->create();
    $userForCompanyB = User::factory()->editConsumables()->forCompany($companyB)->create();

    $this->actingAs($userForCompanyB)
        ->put(route('consumables.update', $consumableForCompanyA), [
            //
        ])
        ->assertStatus(302);
});

test('can update consumable', function () {
    $originalSupplier = Supplier::factory()->create();
    $consumable = Consumable::factory()->create([
        'default_supplier_id' => $originalSupplier->id,
    ]);
    $originalQty = (int) $consumable->qty;
    $newSupplier = Supplier::factory()->create();

    // qty, order_number, purchase_date, purchase_cost are create-only
    // on the parent post-Orders. The edit form drops them and
    // post-create changes live on Order + OrderItem rows via the
    // adjust-quantity flow. default_supplier_id IS editable — it's
    // the parent's "typical supplier" template.
    $editable = [
        'company_id' => Company::factory()->create()->id,
        'name' => 'My Consumable',
        'category_id' => Category::factory()->consumableInkCategory()->create()->id,
        'manufacturer_id' => Manufacturer::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
        'model_number' => '8765',
        'item_no' => '5678',
        'min_amt' => '7',
        'notes' => 'Some Notes',
        'default_supplier_id' => $newSupplier->id,
    ];

    $this->actingAs(User::factory()->createConsumables()->editConsumables()->create())
        ->put(route('consumables.update', $consumable), $editable + [
            'redirect_option' => 'index',
            'category_type' => 'consumable',
            'order_number' => 'ignored-908',
            'qty' => '9999',
            'purchase_cost' => '89.45',
            'purchase_date' => '2024-12-05',
        ])
        ->assertRedirect(route('consumables.index'));

    $this->assertDatabaseHas('consumables', $editable + [
        'qty' => $originalQty,
    ]);
});
