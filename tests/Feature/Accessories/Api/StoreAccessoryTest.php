<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class StoreAccessoryTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.accessories.store'))
            ->assertForbidden();
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInCompanyA = User::factory()->for($companyA)->createAccessories()->create();

        $this->settings->enableMultipleFullCompanySupport();

        // attempt to store an accessory for company B
        $response = $this->actingAsForApi($userInCompanyA)
            ->postJson(route('api.accessories.store'), [
                'category_id' => Category::factory()->forAccessories()->create()->id,
                'name' => 'My Awesome Accessory',
                'qty' => 1,
                'company_id' => $companyB->id,
            ])->assertStatusMessageIs('success');

        $accessory = Accessory::withoutGlobalScopes()->findOrFail($response['payload']['id']);

        $this->assertSame($companyA->id, $accessory->company_id);
        $this->assertDatabaseMissing('accessories', [
            'name' => 'My Awesome Accessory',
            'company_id' => $companyB->id,
        ]);
    }

    public function test_can_store_accessory()
    {
        $category = Category::factory()->forAccessories()->create();
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $supplier = Supplier::factory()->create();

        $this->actingAsForApi(User::factory()->createAccessories()->create())
            ->postJson(route('api.accessories.store'), [
                'name' => 'My Awesome Accessory',
                'qty' => 2,
                'order_number' => '12345',
                'purchase_cost' => 100.00,
                'purchase_date' => '2024-09-18',
                'model_number' => '98765',
                'category_id' => $category->id,
                'company_id' => $company->id,
                'location_id' => $location->id,
                'manufacturer_id' => $manufacturer->id,
                'supplier_id' => $supplier->id,
            ])->assertStatusMessageIs('success');

        $this->assertDatabaseHas('accessories', [
            'name' => 'My Awesome Accessory',
            'qty' => 2,
            'order_number' => '12345',
            'purchase_cost' => 100.00,
            'purchase_date' => '2024-09-18',
            'model_number' => '98765',
            'category_id' => $category->id,
            'company_id' => $company->id,
            'location_id' => $location->id,
            'manufacturer_id' => $manufacturer->id,
            'supplier_id' => $supplier->id,
        ]);
    }
}
