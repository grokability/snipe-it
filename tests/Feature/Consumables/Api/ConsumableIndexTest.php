<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class ConsumableIndexTest extends TestCase
{
    public function test_consumable_index_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $consumableA = Consumable::factory()->for($companyA)->create();
        $consumableB = Consumable::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewConsumables()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewConsumables()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseDoesNotContainInRows($consumableB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.consumables.index'))
            ->assertResponseDoesNotContainInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);
    }

    public function test_consumable_index_returns_expected_search_results()
    {
        Consumable::factory()->count(10)->create();
        Consumable::factory()->count(1)->create(['name' => 'My Test Consumable']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.consumables.index', [
                    'search' => 'My Test Consumable',
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 1,
            ]);

    }

    public function test_consumable_index_filters_all_supported_exact_fields()
    {
        $user = User::factory()->superuser()->create();

        $targetCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $targetCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $targetManufacturer = Manufacturer::factory()->create();
        $otherManufacturer = Manufacturer::factory()->create();
        $targetSupplier = Supplier::factory()->create();
        $otherSupplier = Supplier::factory()->create();
        $targetLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $targetConsumable = Consumable::factory()->create([
            'name' => 'Target Consumable',
            'company_id' => $targetCompany->id,
            'category_id' => $targetCategory->id,
            'model_number' => 'CONS-MODEL-A',
            'manufacturer_id' => $targetManufacturer->id,
            'default_supplier_id' => $targetSupplier->id,
            'location_id' => $targetLocation->id,
            'notes' => 'CONS-NOTES-A',
        ]);

        $otherConsumable = Consumable::factory()->create([
            'name' => 'Other Consumable',
            'company_id' => $otherCompany->id,
            'category_id' => $otherCategory->id,
            'model_number' => 'CONS-MODEL-B',
            'manufacturer_id' => $otherManufacturer->id,
            'default_supplier_id' => $otherSupplier->id,
            'location_id' => $otherLocation->id,
            'notes' => 'CONS-NOTES-B',
        ]);

        // order_number was dropped from the filters list when the parent
        // column was renamed to legacy_order_number and current order
        // numbers moved to the QuantityAdjust action_log per event.
        $filters = [
            'name' => 'Target Consumable',
            'company_id' => $targetCompany->id,
            'category_id' => $targetCategory->id,
            'model_number' => 'CONS-MODEL-A',
            'manufacturer_id' => $targetManufacturer->id,
            'supplier_id' => $targetSupplier->id,
            'location_id' => $targetLocation->id,
            'notes' => 'CONS-NOTES-A',
        ];

        foreach ($filters as $filterKey => $filterValue) {
            $this->actingAsForApi($user)
                ->getJson(route('api.consumables.index', [$filterKey => $filterValue]))
                ->assertOk()
                ->assertResponseContainsInRows($targetConsumable)
                ->assertResponseDoesNotContainInRows($otherConsumable);
        }
    }

    public function test_remaining_includes_checkouts_to_users_outside_viewers_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInCompanyB = User::factory()->forCompany($companyB)->create();

        $consumableForCompanyA = Consumable::factory()
            ->for($companyA)
            // this can happen if FMCS was toggled on after checkouts
            ->checkedOutToUser($userInCompanyB)
            ->checkedOutToUser($userInCompanyB)
            ->create(['qty' => 3]);

        $this->actingAsForApi(User::factory()->viewConsumables()->forCompany($companyA)->create())
            ->getJson(route('api.consumables.index'))
            ->assertOk()
            ->assertJsonPath('rows.0.id', $consumableForCompanyA->id)
            ->assertJsonPath('rows.0.remaining', 1);
    }

    public function test_requestable_remaining_includes_checkouts_to_users_outside_viewers_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInCompanyB = User::factory()->forCompany($companyB)->create();

        $consumableForCompanyA = Consumable::factory()
            ->for($companyA)
            // this can happen if FMCS was toggled on after checkouts
            ->checkedOutToUser($userInCompanyB)
            ->checkedOutToUser($userInCompanyB)
            ->create(['qty' => 3, 'requestable' => true]);

        $this->actingAsForApi(User::factory()->viewRequestableAssets()->forCompany($companyA)->create())
            ->getJson(route('api.consumables.requestable'))
            ->assertOk()
            ->assertJsonPath('rows.0.id', $consumableForCompanyA->id)
            ->assertJsonPath('rows.0.remaining', 1);
    }
}
