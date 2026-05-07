<?php

namespace Tests\Feature\Components\Api;

use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class ComponentIndexTest extends TestCase
{
    public function test_component_index_adheres_to_company_scoping()
    {
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
    }

    public function test_component_index_filters_all_supported_exact_fields()
    {
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
            'order_number' => 'COMP-ORDER-A',
            'category_id' => $targetCategory->id,
            'supplier_id' => $targetSupplier->id,
            'manufacturer_id' => $targetManufacturer->id,
            'model_number' => 'COMP-MODEL-A',
            'location_id' => $targetLocation->id,
            'notes' => 'COMP-NOTES-A',
        ]);

        $otherComponent = Component::factory()->create([
            'name' => 'Other Component',
            'company_id' => $otherCompany->id,
            'order_number' => 'COMP-ORDER-B',
            'category_id' => $otherCategory->id,
            'supplier_id' => $otherSupplier->id,
            'manufacturer_id' => $otherManufacturer->id,
            'model_number' => 'COMP-MODEL-B',
            'location_id' => $otherLocation->id,
            'notes' => 'COMP-NOTES-B',
        ]);

        $filters = [
            'name' => 'Target Component',
            'company_id' => $targetCompany->id,
            'order_number' => 'COMP-ORDER-A',
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
    }
}
