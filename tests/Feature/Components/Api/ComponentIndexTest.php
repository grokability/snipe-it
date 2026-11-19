<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('component index adheres to company scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $componentA = Component::factory()->for($companyA)->create();
    $componentB = Component::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->viewComponents()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewComponents()->make());

    $this->settings->disableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.components.index'))
        ->assertResponseContainsInRows($componentA)
        ->assertResponseContainsInRows($componentB);

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.components.index'))
        ->assertResponseContainsInRows($componentA)
        ->assertResponseContainsInRows($componentB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.components.index'))
        ->assertResponseContainsInRows($componentA)
        ->assertResponseContainsInRows($componentB);

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.components.index'))
        ->assertResponseContainsInRows($componentA)
        ->assertResponseContainsInRows($componentB);

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.components.index'))
        ->assertResponseContainsInRows($componentA)
        ->assertResponseDoesNotContainInRows($componentB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.components.index'))
        ->assertResponseDoesNotContainInRows($componentA)
        ->assertResponseContainsInRows($componentB);
});

test('component index filters all supported exact fields', function () {
    $user = User::factory()->superuser()->create();

    $targetCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $targetCategory = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $targetSupplier = Supplier::factory()->create();
    $otherSupplier = Supplier::factory()->create();
    $targetManufacturer = Manufacturer::factory()->create();
    $otherManufacturer = Manufacturer::factory()->create();
    $targetLocation = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $targetComponent = Component::factory()->create([
        'name' => 'Target Component',
        'company_id' => $targetCompany->id,
        'category_id' => $targetCategory->id,
        'default_supplier_id' => $targetSupplier->id,
        'manufacturer_id' => $targetManufacturer->id,
        'model_number' => 'COMP-MODEL-A',
        'location_id' => $targetLocation->id,
        'notes' => 'COMP-NOTES-A',
    ]);

    $otherComponent = Component::factory()->create([
        'name' => 'Other Component',
        'company_id' => $otherCompany->id,
        'category_id' => $otherCategory->id,
        'default_supplier_id' => $otherSupplier->id,
        'manufacturer_id' => $otherManufacturer->id,
        'model_number' => 'COMP-MODEL-B',
        'location_id' => $otherLocation->id,
        'notes' => 'COMP-NOTES-B',
    ]);

    // order_number was dropped from the filters list when the parent
    // column was renamed to legacy_order_number and current order
    // numbers moved to the QuantityAdjust action_log per event.
    $filters = [
        'name' => 'Target Component',
        'company_id' => $targetCompany->id,
        'category_id' => $targetCategory->id,
        'supplier_id' => $targetSupplier->id,
        'manufacturer_id' => $targetManufacturer->id,
        'model_number' => 'COMP-MODEL-A',
        'location_id' => $targetLocation->id,
        'notes' => 'COMP-NOTES-A',
    ];

    foreach ($filters as $filterKey => $filterValue) {
        $this->actingAsForApi($user)
            ->getJson(route('api.components.index', [$filterKey => $filterValue]))
            ->assertOk()
            ->assertResponseContainsInRows($targetComponent)
            ->assertResponseDoesNotContainInRows($otherComponent);
    }
});

test('can sort components by raw remaining count', function () {
    // Requested in issue #18505 alongside the percent_remaining sort:
    // sort by absolute stock left (qty - active assignments). The
    // scope references the withSum-added `sum_unconstrained_assets`
    // alias, so a regression in the API's eager-load order would
    // surface here as a SQL error or a null-count sort.
    $user = User::factory()->viewComponents()->create();

    $lots = Component::factory()->create(['name' => 'Lots left', 'qty' => 10]);
    $some = Component::factory()->create(['name' => 'Some left', 'qty' => 10]);
    $none = Component::factory()->create(['name' => 'None left', 'qty' => 10]);

    $asset = Asset::factory()->create();

    // assigned_qty on the pivot is what withSum('unconstrainedAssets')
    // sums into sum_unconstrained_assets, so one pivot row per
    // component with a distinct qty gives distinct remaining counts.
    $lots->assets()->attach($asset->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => $user->id]); // remaining = 9
    $some->assets()->attach($asset->id, ['assigned_qty' => 5, 'created_at' => now(), 'created_by' => $user->id]); // remaining = 5
    $none->assets()->attach($asset->id, ['assigned_qty' => 10, 'created_at' => now(), 'created_by' => $user->id]); // remaining = 0

    $descRows = $this->actingAsForApi($user)
        ->getJson(route('api.components.index', ['sort' => 'remaining', 'order' => 'desc']))
        ->assertOk()
        ->json('rows');

    $descNames = array_column($descRows, 'name');
    $descRelevant = array_values(array_intersect($descNames, ['Lots left', 'Some left', 'None left']));
    expect($descRelevant)->toBe(['Lots left', 'Some left', 'None left']);

    $ascRows = $this->actingAsForApi($user)
        ->getJson(route('api.components.index', ['sort' => 'remaining', 'order' => 'asc']))
        ->assertOk()
        ->json('rows');

    $ascNames = array_column($ascRows, 'name');
    $ascRelevant = array_values(array_intersect($ascNames, ['Lots left', 'Some left', 'None left']));
    expect($ascRelevant)->toBe(['None left', 'Some left', 'Lots left']);
});

test('can sort components by order number', function () {
    $user = User::factory()->viewComponents()->create();

    $componentA = Component::factory()->create(['name' => 'Component A', 'qty' => 1]);
    $componentB = Component::factory()->create(['name' => 'Component B', 'qty' => 1]);
    $componentC = Component::factory()->create(['name' => 'Component C', 'qty' => 1]);

    $componentA->orderItems()->first()->order->update(['order_number' => 'PO-0002']);
    $componentB->orderItems()->first()->order->update(['order_number' => 'PO-0003']);
    $componentC->orderItems()->first()->order->update(['order_number' => 'PO-0001']);

    $names = ['Component A', 'Component B', 'Component C'];

    $ascendingResponse = $this->actingAsForApi($user)
        ->getJson(route('api.components.index', ['sort' => 'order_number', 'order' => 'asc']))
        ->assertOk()
        ->json('rows');

    // filter out any other components that may exist in response and only grab the ones we seeded above
    $ascendingRelevant = array_values(array_intersect(array_column($ascendingResponse, 'name'), $names));
    expect($ascendingRelevant)->toBe(['Component C', 'Component A', 'Component B']);

    $descendingRows = $this->actingAsForApi($user)
        ->getJson(route('api.components.index', ['sort' => 'order_number', 'order' => 'desc']))
        ->assertOk()
        ->json('rows');

    // filter out any other components that may exist in response and only grab the ones we seeded above
    $descendingRelevant = array_values(array_intersect(array_column($descendingRows, 'name'), $names));
    expect($descendingRelevant)->toBe(['Component B', 'Component A', 'Component C']);
});
