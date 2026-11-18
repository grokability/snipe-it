<?php

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires permission', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->patchJson(route('api.accessories.update', $accessory))
        ->assertForbidden();
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->create(['name' => 'A Name to Change']);
    $accessoryB = Accessory::factory()->for($companyB)->create(['name' => 'A Name to Change']);
    $accessoryC = Accessory::factory()->for($companyB)->create(['name' => 'A Name to Change']);

    $superuser = User::factory()->superuser()->create();
    $userInCompanyA = $companyA->users()->save(User::factory()->editAccessories()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->editAccessories()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->patchJson(route('api.accessories.update', $accessoryB), ['name' => 'New Name'])
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($userInCompanyB)
        ->patchJson(route('api.accessories.update', $accessoryA), ['name' => 'New Name'])
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superuser)
        ->patchJson(route('api.accessories.update', $accessoryC), ['name' => 'New Name'])
        ->assertOk();

    expect($accessoryA->fresh()->name)->toEqual('A Name to Change');
    expect($accessoryB->fresh()->name)->toEqual('A Name to Change');
    expect($accessoryC->fresh()->name)->toEqual('New Name');
});

test('prevents cross tenant company reassignment when fmcs enabled', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessory = Accessory::factory()->for($companyA)->create();
    $userInCompanyA = User::factory()->forCompany($companyA)->editAccessories()->create();

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->patchJson(route('api.accessories.update', $accessory), [
            'company_id' => $companyB->id,
        ])
        ->assertStatusMessageIs('error');

    expect($accessory->fresh()->company_id)->toBe($companyA->id);
});

test('allows superuser company reassignment when fmcs enabled', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessory = Accessory::factory()->for($companyA)->create();
    $superuser = User::factory()->superuser()->withoutCompany()->create();

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superuser)
        ->patchJson(route('api.accessories.update', $accessory), [
            'company_id' => $companyB->id,
        ])
        ->assertStatusMessageIs('success');

    expect($accessory->fresh()->company_id)->toBe($companyB->id);
});

test('can update accessory via patch', function () {
    [$categoryA, $categoryB] = Category::factory()->count(2)->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    [$locationA, $locationB] = Location::factory()->count(2)->create();
    [$manufacturerA, $manufacturerB] = Manufacturer::factory()->count(2)->create();
    [$supplierA, $supplierB] = Supplier::factory()->count(2)->create();

    $accessory = Accessory::factory()->create([
        'name' => 'A Name to Change',
        'qty' => 5,
        'model_number' => 'ABC098',
        'category_id' => $categoryA->id,
        'company_id' => $companyA->id,
        'location_id' => $locationA->id,
        'manufacturer_id' => $manufacturerA->id,
        'default_supplier_id' => $supplierA->id,
    ]);

    // Payload shape preserved for API back-compat. Acquisition-only
    // fields (order_number, purchase_date, purchase_cost, and the
    // per-Order supplier_id) don't touch the parent — post-create
    // changes flow through Orders + OrderItems via the
    // adjust-quantity endpoint. default_supplier_id IS editable
    // (parent template).
    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->patchJson(route('api.accessories.update', $accessory), [
            'name' => 'A New Name',
            'qty' => 10,
            'order_number' => 'B54321',
            'purchase_cost' => 199.99,
            'model_number' => 'XYZ123',
            'category_id' => $categoryB->id,
            'company_id' => $companyB->id,
            'location_id' => $locationB->id,
            'manufacturer_id' => $manufacturerB->id,
            'default_supplier_id' => $supplierB->id,
        ])
        ->assertOk();

    $accessory = $accessory->fresh();
    expect($accessory->name)->toEqual('A New Name');

    // qty stays supported on the PATCH endpoint for API back-compat:
    // the update controller routes qty deltas through adjustQuantity
    // so a payload with qty=10 (original 5) writes a +5 OrderItem
    // and moves the parent qty to 10.
    expect($accessory->qty)->toEqual(10);
    expect($accessory->default_supplier_id)->toEqual($supplierB->id);
    expect($accessory->model_number)->toEqual('XYZ123');
    expect($accessory->category_id)->toEqual($categoryB->id);
    expect($accessory->company_id)->toEqual($companyB->id);
    expect($accessory->location_id)->toEqual($locationB->id);
    expect($accessory->manufacturer_id)->toEqual($manufacturerB->id);
});

test('update logs changed fields in log meta', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'name' => 'Old Name']);

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->patchJson(route('api.accessories.update', $accessory), ['qty' => 10, 'name' => 'New Name']);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'update')
        ->latest()
        ->first();

    expect($log)->not->toBeNull('No update log entry was created');
    expect($log->log_meta)->not->toBeNull('log_meta was not stored');

    // qty change writes its own QuantityAdjust log (asserted below);
    // it does not appear in the update log_meta because the fill()
    // deliberately excludes qty to route it through adjustQuantity.
    $meta = json_decode($log->log_meta, true);
    $this->assertArrayNotHasKey('qty', $meta);
    expect($meta['name']['old'])->toEqual('Old Name');
    expect($meta['name']['new'])->toEqual('New Name');
});

test('qty change via api creates quantity adjust log', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->patchJson(route('api.accessories.update', $accessory), ['qty' => 12, 'order_number' => 'PO-API'])
        ->assertOk();

    expect((int) $accessory->fresh()->qty)->toBe(12);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', \App\Enums\ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(7);
    expect($log->orderItem->order->order_number)->toBe('PO-API');
    expect($log->note)->not->toBeEmpty('Adjustment note must be synthesized when the API caller omits one');
});

test('qty only change via api creates no update log entry', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'name' => 'Same Name']);

    $updateLogsBefore = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'update')
        ->count();

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->patchJson(route('api.accessories.update', $accessory), ['qty' => 8, 'name' => 'Same Name'])
        ->assertOk();

    $updateLogsAfter = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'update')
        ->count();

    // Only qty changed. The QuantityAdjust log captures that change;
    // no separate 'update' log should exist because nothing else was dirty.
    expect($updateLogsAfter)->toBe($updateLogsBefore);
    expect(Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', \App\Enums\ActionType::QuantityAdjust->value)
        ->count())->toBe(1);
});

test('qty change below in use returns 422', function () {
    $accessory = Accessory::factory()->create(['qty' => 10]);
    \App\Models\AccessoryCheckout::factory()->count(4)->create([
        'accessory_id' => $accessory->id,
        'assigned_type' => User::class,
        'assigned_to' => User::factory()->create()->id,
    ]);

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->patchJson(route('api.accessories.update', $accessory), ['qty' => 3]) // below the 4 in use
        ->assertStatus(422);

    expect((int) $accessory->fresh()->qty)->toBe(10);
});

test('no op update does not create log entry', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'name' => 'Same Name']);

    $before = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->count();

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->patchJson(route('api.accessories.update', $accessory), ['qty' => 5, 'name' => 'Same Name']);

    $after = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->count();

    expect($after)->toEqual($before, 'A spurious log entry was created for a no-op update');
});
