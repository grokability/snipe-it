<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.accessories.index'))
        ->assertForbidden();
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->create(['name' => 'Accessory A']);
    $accessoryB = Accessory::factory()->for($companyB)->create(['name' => 'Accessory B']);
    $accessoryC = Accessory::factory()->for($companyB)->create(['name' => 'Accessory C']);

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->viewAccessories()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewAccessories()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.accessories.index'))
        ->assertOk()
        ->assertResponseContainsInRows($accessoryA)
        ->assertResponseDoesNotContainInRows($accessoryB)
        ->assertResponseDoesNotContainInRows($accessoryC);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.accessories.index'))
        ->assertOk()
        ->assertResponseDoesNotContainInRows($accessoryA)
        ->assertResponseContainsInRows($accessoryB)
        ->assertResponseContainsInRows($accessoryC);

    $this->actingAsForApi($superUser)
        ->getJson(route('api.accessories.index'))
        ->assertOk()
        ->assertResponseContainsInRows($accessoryA)
        ->assertResponseContainsInRows($accessoryB)
        ->assertResponseContainsInRows($accessoryC);
});

test('can get accessories', function () {
    $user = User::factory()->viewAccessories()->create();

    $accessoryA = Accessory::factory()->create(['name' => 'Accessory A']);
    $accessoryB = Accessory::factory()->create(['name' => 'Accessory B']);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index'))
        ->assertOk()
        ->assertResponseContainsInRows($accessoryA)
        ->assertResponseContainsInRows($accessoryB);
});

test('can filter accessories by searchable count alias', function () {
    $this->markIncompleteIfSqlite('This test is not compatible with SQLite');
    $user = User::factory()->viewAccessories()->create();

    $targetAccessory = Accessory::factory()->create(['name' => 'Accessory With Two Checkouts']);
    $otherAccessory = Accessory::factory()->create(['name' => 'Accessory With One Checkout']);

    AccessoryCheckout::factory()->count(2)->create(['accessory_id' => $targetAccessory->id]);
    AccessoryCheckout::factory()->create(['accessory_id' => $otherAccessory->id]);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', [
            'filter' => json_encode(['checkouts_count' => 2]),
            'sort' => 'id',
            'order' => 'asc',
            'offset' => '0',
            'limit' => '20',
        ]))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->where('rows.0.name', 'Accessory With Two Checkouts')->etc());
});

test('can filter accessories by remaining count', function () {
    $this->markIncompleteIfSqlite('This test is not compatible with SQLite');
    $user = User::factory()->viewAccessories()->create();

    $available = Accessory::factory()->create(['name' => 'Some left', 'qty' => 2]);
    $empty = Accessory::factory()->create(['name' => 'None left', 'qty' => 1]);

    AccessoryCheckout::factory()->create(['accessory_id' => $available->id]);
    AccessoryCheckout::factory()->create(['accessory_id' => $empty->id]);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', [
            'filter' => json_encode(['remaining' => 'gt:0']),
        ]))
        ->assertOk()
        ->assertResponseContainsInRows($available)
        ->assertResponseDoesNotContainInRows($empty);
});

test('can filter accessories by checkouts count range', function () {
    $this->markIncompleteIfSqlite('This test is not compatible with SQLite');
    $user = User::factory()->viewAccessories()->create();

    $checkedOut = Accessory::factory()->create(['name' => 'Checked out']);
    $notCheckedOut = Accessory::factory()->create(['name' => 'Not checked out']);

    AccessoryCheckout::factory()->create(['accessory_id' => $checkedOut->id]);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', [
            'filter' => json_encode(['checkouts_count' => 'gt:0']),
        ]))
        ->assertOk()
        ->assertResponseContainsInRows($checkedOut)
        ->assertResponseDoesNotContainInRows($notCheckedOut);
});

test('can filter accessories by all supported exact fields', function () {
    $user = User::factory()->superuser()->create();

    $targetCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $targetCategory = Category::factory()->forAccessories()->create();
    $otherCategory = Category::factory()->forAccessories()->create();
    $targetManufacturer = Manufacturer::factory()->create();
    $otherManufacturer = Manufacturer::factory()->create();
    $targetSupplier = Supplier::factory()->create();
    $otherSupplier = Supplier::factory()->create();
    $targetLocation = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $targetAccessory = Accessory::factory()->create([
        'name' => 'Target Accessory',
        'company_id' => $targetCompany->id,
        'category_id' => $targetCategory->id,
        'manufacturer_id' => $targetManufacturer->id,
        'default_supplier_id' => $targetSupplier->id,
        'location_id' => $targetLocation->id,
        'notes' => 'NOTE-A',
    ]);

    $otherAccessory = Accessory::factory()->create([
        'name' => 'Other Accessory',
        'company_id' => $otherCompany->id,
        'category_id' => $otherCategory->id,
        'manufacturer_id' => $otherManufacturer->id,
        'default_supplier_id' => $otherSupplier->id,
        'location_id' => $otherLocation->id,
        'notes' => 'NOTE-B',
    ]);

    // order_number was dropped from the filters list when the parent
    // column was renamed to legacy_order_number and current order
    // numbers moved to the QuantityAdjust action_log per event.
    $filters = [
        'company_id' => $targetCompany->id,
        'category_id' => $targetCategory->id,
        'manufacturer_id' => $targetManufacturer->id,
        'supplier_id' => $targetSupplier->id,
        'location_id' => $targetLocation->id,
        'notes' => 'NOTE-A',
    ];

    foreach ($filters as $filterKey => $filterValue) {
        $this->actingAsForApi($user)
            ->getJson(route('api.accessories.index', [$filterKey => $filterValue]))
            ->assertOk()
            ->assertResponseContainsInRows($targetAccessory)
            ->assertResponseDoesNotContainInRows($otherAccessory);
    }
});

test('can sort accessories by raw remaining count', function () {
    // Requested in issue #18505 alongside the percent_remaining sort:
    // sort by absolute stock left (qty - active checkouts). The scope
    // references the withCount-added `checkouts_count` alias, so a
    // regression in the API's eager-load order would surface here as
    // a SQL error or a null-count sort.
    $user = User::factory()->viewAccessories()->create();

    $lots = Accessory::factory()->create(['name' => 'Lots left', 'qty' => 10]);
    $some = Accessory::factory()->create(['name' => 'Some left', 'qty' => 10]);
    $none = Accessory::factory()->create(['name' => 'None left', 'qty' => 10]);

    AccessoryCheckout::factory()->count(1)->create(['accessory_id' => $lots->id]);
    // remaining = 9
    AccessoryCheckout::factory()->count(5)->create(['accessory_id' => $some->id]);
    // remaining = 5
    AccessoryCheckout::factory()->count(10)->create(['accessory_id' => $none->id]);

    // remaining = 0
    $descRows = $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', ['sort' => 'remaining', 'order' => 'desc']))
        ->assertOk()
        ->json('rows');

    $descNames = array_column($descRows, 'name');
    $descRelevant = array_values(array_intersect($descNames, ['Lots left', 'Some left', 'None left']));
    expect($descRelevant)->toBe(['Lots left', 'Some left', 'None left']);

    $ascRows = $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', ['sort' => 'remaining', 'order' => 'asc']))
        ->assertOk()
        ->json('rows');

    $ascNames = array_column($ascRows, 'name');
    $ascRelevant = array_values(array_intersect($ascNames, ['Lots left', 'Some left', 'None left']));
    expect($ascRelevant)->toBe(['None left', 'Some left', 'Lots left']);
});

test('index response includes distinct order numbers per accessory', function () {
    // The Accessory observer writes one Order + OrderItem per create()
    // with a null order_number. Set explicit order numbers on those
    // seed rows so the transformer's `orders` array has non-null
    // values to surface. Multiple orders on one accessory must land
    // in the output as a distinct list (feeds ordersSummaryFormatter).
    $user = User::factory()->viewAccessories()->create();

    $accessory = Accessory::factory()->create(['name' => 'Accessory With Orders']);
    $accessory->orderItems()->first()->order->update(['order_number' => 'ORD-1001']);

    $secondOrder = Order::create(['order_number' => 'ORD-1002']);
    OrderItem::create([
        'order_id' => $secondOrder->id,
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'qty' => 5,
        'price' => 10,
    ]);

    $rows = $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index'))
        ->assertOk()
        ->json('rows');

    $row = collect($rows)->firstWhere('id', $accessory->id);

    expect($row)->not->toBeNull();
    expect($row['orders'])->toBeArray();
    expect($row['orders'])->toEqualCanonicalizing(['ORD-1001', 'ORD-1002']);
});

test('advanced search filter on order number returns only matching accessories', function () {
    // Column is wired to searchableRelations['orders' => ['order_number']],
    // so a `filter[orders]=X` query threads through the Searchable
    // trait to a whereHas('orders') LIKE match on orders.order_number.
    // Prefix operators (is:, not:, is:null, is:not_null) are honored
    // by the same code path; this test pins the default LIKE path so
    // the relation is provably wired.
    $user = User::factory()->superuser()->create();

    $targetAccessory = Accessory::factory()->create(['name' => 'Target']);
    $otherAccessory = Accessory::factory()->create(['name' => 'Other']);

    $targetAccessory->orderItems()->first()->order->update(['order_number' => 'ADV-SEARCH-777']);
    $otherAccessory->orderItems()->first()->order->update(['order_number' => 'UNRELATED-999']);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', [
            'filter' => json_encode(['orders' => 'ADV-SEARCH-777']),
        ]))
        ->assertOk()
        ->assertResponseContainsInRows($targetAccessory)
        ->assertResponseDoesNotContainInRows($otherAccessory);
});

test('advanced search category csv of ids matches by where in', function () {
    // Multi-select scrollbox path: the modal widget submits an array
    // of category ids as the filter value
    $user = User::factory()->superuser()->create();

    $cat1 = Category::factory()->forAccessories()->create(['name' => 'Peripherals']);
    $cat2 = Category::factory()->forAccessories()->create(['name' => 'Cables']);
    $cat3 = Category::factory()->forAccessories()->create(['name' => 'Software']);

    $inCat1 = Accessory::factory()->create(['category_id' => $cat1->id, 'name' => 'Mouse']);
    $inCat2 = Accessory::factory()->create(['category_id' => $cat2->id, 'name' => 'HDMI']);
    $inCat3 = Accessory::factory()->create(['category_id' => $cat3->id, 'name' => 'License XYZ']);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', [
            'filter' => json_encode(['category' => $cat1->id.','.$cat2->id]),
        ]))
        ->assertOk()
        ->assertResponseContainsInRows($inCat1)
        ->assertResponseContainsInRows($inCat2)
        ->assertResponseDoesNotContainInRows($inCat3);
});

test('advanced search category single name still matches via like path', function () {
    // Guard that the new whereIn branch does not swallow single-
    // value name lookups.
    $user = User::factory()->superuser()->create();

    $target = Category::factory()->forAccessories()->create(['name' => 'Peripherals']);
    $other = Category::factory()->forAccessories()->create(['name' => 'Cables']);

    // Pin names so a Faker collision (AccessoryFactory draws from
    // a 4-value pool) does not false-fail the "other not in rows"
    // check when both accessories randomly land on the same name.
    $targetAccessory = Accessory::factory()->create(['category_id' => $target->id, 'name' => 'In-Peripherals Pick']);
    $otherAccessory = Accessory::factory()->create(['category_id' => $other->id, 'name' => 'In-Cables Pick']);

    $this->actingAsForApi($user)
        ->getJson(route('api.accessories.index', [
            'filter' => json_encode(['category' => 'Peripherals']),
        ]))
        ->assertOk()
        ->assertResponseContainsInRows($targetAccessory)
        ->assertResponseDoesNotContainInRows($otherAccessory);
});
