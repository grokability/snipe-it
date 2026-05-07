<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
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
            'order_number' => 'ORDER-A',
            'category_id' => $targetCategory->id,
            'manufacturer_id' => $targetManufacturer->id,
            'supplier_id' => $targetSupplier->id,
            'location_id' => $targetLocation->id,
            'notes' => 'NOTE-A',
        ]);

        $otherAccessory = Accessory::factory()->create([
            'name' => 'Other Accessory',
            'company_id' => $otherCompany->id,
            'order_number' => 'ORDER-B',
            'category_id' => $otherCategory->id,
            'manufacturer_id' => $otherManufacturer->id,
            'supplier_id' => $otherSupplier->id,
            'location_id' => $otherLocation->id,
            'notes' => 'NOTE-B',
        ]);

        $filters = [
            'company_id' => $targetCompany->id,
            'order_number' => 'ORDER-A',
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
}
