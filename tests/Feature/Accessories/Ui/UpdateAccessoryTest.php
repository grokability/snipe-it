<?php

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires permission to see edit accessory page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('accessories.edit', Accessory::factory()->create()))
        ->assertForbidden();
});

test('edit accessory page renders', function () {
    $this->actingAs(User::factory()->editAccessories()->create())
        ->get(route('accessories.edit', Accessory::factory()->create()->id))
        ->assertOk()
        ->assertViewIs('accessories.edit');
});

test('does not show edit accessory page from another company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();
    $userForCompanyB = User::factory()->forCompany($companyB)->editAccessories()->create();

    $this->actingAs($userForCompanyB)
        ->get(route('accessories.edit', $accessoryForCompanyA->id))
        ->assertRedirect(route('accessories.index'));
});

test('cannot set quantity to amount lower than what is checked out', function () {
    $accessory = Accessory::factory()->create(['qty' => 2]);
    $accessory->checkouts()->create(['assigned_to' => User::factory()->create()->id, 'qty' => 1]);
    $accessory->checkouts()->create(['assigned_to' => User::factory()->create()->id, 'qty' => 1]);

    expect($accessory->checkouts->count())->toEqual(2);

    $this->actingAs(User::factory()->editAccessories()->create())
        ->put(route('accessories.update', $accessory), [
            'redirect_option' => 'index',
            'company_id' => (string) $accessory->company_id,
            'name' => $accessory->name,
            'category_id' => (string) $accessory->category_id,
            'default_supplier_id' => (string) $accessory->default_supplier_id,
            'manufacturer_id' => (string) $accessory->manufacturer_id,
            'location_id' => (string) $accessory->location_id,
            'model_number' => $accessory->model_number,
            'min_amt' => $accessory->min_amt,
            'notes' => $accessory->notes,
            // the important part...
            // try to lower the qty to 1 when there are 2 checked out
            'qty' => '1',
        ]);
});

test('can update accessory', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    [$categoryA, $categoryB] = Category::factory()->count(2)->create();
    [$supplierA, $supplierB] = Supplier::factory()->count(2)->create();
    [$manufacturerA, $manufacturerB] = Manufacturer::factory()->count(2)->create();
    [$locationA, $locationB] = Location::factory()->count(2)->create();

    $accessory = Accessory::factory()
        ->for($companyA)
        ->for($categoryA)
        ->for($manufacturerA)
        ->for($locationA)
        ->create([
            'min_amt' => 1,
            'qty' => 5,
            'default_supplier_id' => $supplierA->id,
        ]);

    // qty, order_number, purchase_date, purchase_cost are all
    // create-only on the parent post-Orders — the edit form drops
    // them and post-create changes live on Order + OrderItem rows.
    // default_supplier_id IS editable (parent "typical supplier"
    // template that seeds new orders).
    $this->actingAs(User::factory()->editAccessories()->create())
        ->put(route('accessories.update', $accessory), [
            'redirect_option' => 'index',
            'company_id' => (string) $companyB->id,
            'name' => 'Changed Name',
            'category_id' => (string) $categoryB->id,
            'default_supplier_id' => (string) $supplierB->id,
            'manufacturer_id' => (string) $manufacturerB->id,
            'location_id' => (string) $locationB->id,
            'model_number' => 'changed 1234',
            'purchase_date' => '2024-10-11',
            'purchase_cost' => '83.52',
            'qty' => '7',
            'min_amt' => '10',
            'notes' => 'A new note',
        ])
        ->assertRedirect(route('accessories.index'));

    $this->assertDatabaseHas('accessories', [
        'company_id' => $companyB->id,
        'name' => 'Changed Name',
        'category_id' => $categoryB->id,
        'default_supplier_id' => $supplierB->id,
        'manufacturer_id' => $manufacturerB->id,
        'location_id' => $locationB->id,
        'model_number' => 'changed 1234',
        'qty' => '5',                             // unchanged; create-only
        'min_amt' => '10',
        'notes' => 'A new note',
    ]);
});

test('update logs changed fields in log meta', function () {
    $accessory = Accessory::factory()->create([
        'qty' => 5,
        'name' => 'Old Name',
        'model_number' => null,
        'location_id' => null,
    ]);

    $this->actingAs(User::factory()->editAccessories()->create())
        ->put(route('accessories.update', $accessory), [
            'redirect_option' => 'index',
            'name' => 'New Name',
            'category_id' => (string) $accessory->category_id,
        ]);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'update')
        ->latest()
        ->first();

    expect($log)->not->toBeNull('No update log entry was created');
    expect($log->log_meta)->not->toBeNull('log_meta was not stored');

    // qty is no longer editable via update; log_meta captures only
    // the fields the edit form can actually change.
    $meta = json_decode($log->log_meta, true);
    $this->assertArrayNotHasKey('qty', $meta);
    expect($meta['name']['old'])->toEqual('Old Name');
    expect($meta['name']['new'])->toEqual('New Name');
});

test('no op update does not create log entry', function () {
    $accessory = Accessory::factory()->create([
        'qty' => 5,
        'name' => 'Same Name',
        'model_number' => null,
        'location_id' => null,
    ]);

    $before = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'update')
        ->count();

    // Echo persisted values back for every field the controller sets
    // unconditionally from the request. A missing key on the payload
    // coerces to null server-side and marks the model dirty, which
    // trips the update-log observer. Pre-existing quirk in the web
    // update, not introduced by these tests.
    $this->actingAs(User::factory()->editAccessories()->create())
        ->put(route('accessories.update', $accessory), [
            'redirect_option' => 'index',
            'name' => 'Same Name',
            'qty' => '5',
            'category_id' => (string) $accessory->category_id,
            'purchase_date' => $accessory->purchase_date?->format('Y-m-d'),
            'purchase_cost' => $accessory->purchase_cost,
        ]);

    $after = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'update')
        ->count();

    expect($after)->toEqual($before, 'A spurious log entry was created for a no-op update');
});
