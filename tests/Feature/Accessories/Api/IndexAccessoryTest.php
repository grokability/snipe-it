<?php

namespace Tests\Feature\Accessories\Api;

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
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class IndexAccessoryTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.accessories.index'))
            ->assertForbidden();
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
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
    }

    public function test_can_get_accessories()
    {
        $user = User::factory()->viewAccessories()->create();

        $accessoryA = Accessory::factory()->create(['name' => 'Accessory A']);
        $accessoryB = Accessory::factory()->create(['name' => 'Accessory B']);

        $this->actingAsForApi($user)
            ->getJson(route('api.accessories.index'))
            ->assertOk()
            ->assertResponseContainsInRows($accessoryA)
            ->assertResponseContainsInRows($accessoryB);
    }

    public function test_can_filter_accessories_by_searchable_count_alias()
    {
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
    }

    public function test_can_filter_accessories_by_all_supported_exact_fields()
    {
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
    }

    public function test_can_sort_accessories_by_raw_remaining_count()
    {
        // Requested in issue #18505 alongside the percent_remaining sort:
        // sort by absolute stock left (qty - active checkouts). The scope
        // references the withCount-added `checkouts_count` alias, so a
        // regression in the API's eager-load order would surface here as
        // a SQL error or a null-count sort.
        $user = User::factory()->viewAccessories()->create();

        $lots = Accessory::factory()->create(['name' => 'Lots left', 'qty' => 10]);
        $some = Accessory::factory()->create(['name' => 'Some left', 'qty' => 10]);
        $none = Accessory::factory()->create(['name' => 'None left', 'qty' => 10]);

        AccessoryCheckout::factory()->count(1)->create(['accessory_id' => $lots->id]);   // remaining = 9
        AccessoryCheckout::factory()->count(5)->create(['accessory_id' => $some->id]);   // remaining = 5
        AccessoryCheckout::factory()->count(10)->create(['accessory_id' => $none->id]);  // remaining = 0

        $descRows = $this->actingAsForApi($user)
            ->getJson(route('api.accessories.index', ['sort' => 'remaining', 'order' => 'desc']))
            ->assertOk()
            ->json('rows');

        $descNames = array_column($descRows, 'name');
        $descRelevant = array_values(array_intersect($descNames, ['Lots left', 'Some left', 'None left']));
        $this->assertSame(['Lots left', 'Some left', 'None left'], $descRelevant);

        $ascRows = $this->actingAsForApi($user)
            ->getJson(route('api.accessories.index', ['sort' => 'remaining', 'order' => 'asc']))
            ->assertOk()
            ->json('rows');

        $ascNames = array_column($ascRows, 'name');
        $ascRelevant = array_values(array_intersect($ascNames, ['Lots left', 'Some left', 'None left']));
        $this->assertSame(['None left', 'Some left', 'Lots left'], $ascRelevant);
    }

    public function test_index_response_includes_distinct_order_numbers_per_accessory()
    {
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

        $this->assertNotNull($row);
        $this->assertIsArray($row['orders']);
        $this->assertEqualsCanonicalizing(['ORD-1001', 'ORD-1002'], $row['orders']);
    }

    public function test_advanced_search_filter_on_order_number_returns_only_matching_accessories()
    {
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
    }

    public function test_advanced_search_category_csv_of_ids_matches_by_where_in(): void
    {
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
                'filter' => json_encode(['category' => $cat1->id . ',' . $cat2->id]),
            ]))
            ->assertOk()
            ->assertResponseContainsInRows($inCat1)
            ->assertResponseContainsInRows($inCat2)
            ->assertResponseDoesNotContainInRows($inCat3);
    }

    public function test_advanced_search_category_single_name_still_matches_via_like_path(): void
    {
        // Guard that the new whereIn branch does not swallow single-
        // value name lookups. A hand-typed "Peripherals" value in the
        // category filter should still fall through to the relation-
        // LIKE path, same as pre-prototype behavior, because the value
        // is not an integer CSV.
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
    }
}
