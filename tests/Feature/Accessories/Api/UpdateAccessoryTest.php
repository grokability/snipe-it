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

class UpdateAccessoryTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.accessories.update', $accessory))
            ->assertForbidden();
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
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

        $this->assertEquals('A Name to Change', $accessoryA->fresh()->name);
        $this->assertEquals('A Name to Change', $accessoryB->fresh()->name);
        $this->assertEquals('New Name', $accessoryC->fresh()->name);
    }

    public function testCanUpdateAccessoryViaPatch()
    {
        [$categoryA, $categoryB] = Category::factory()->count(2)->create();
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        [$locationA, $locationB] = Location::factory()->count(2)->create();
        [$manufacturerA, $manufacturerB] = Manufacturer::factory()->count(2)->create();
        [$supplierA, $supplierB] = Supplier::factory()->count(2)->create();

        $accessory = Accessory::factory()->create([
            'name' => 'A Name to Change',
            'qty' => 5,
            'order_number' => 'A12345',
            'purchase_cost' => 99.99,
            'model_number' => 'ABC098',
            'category_id' => $categoryA->id,
            'company_id' => $companyA->id,
            'location_id' => $locationA->id,
            'manufacturer_id' => $manufacturerA->id,
            'supplier_id' => $supplierA->id,
        ]);

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
                'supplier_id' => $supplierB->id,
            ])
            ->assertOk();

        $accessory = $accessory->fresh();
        $this->assertEquals('A New Name', $accessory->name);
        $this->assertEquals(10, $accessory->qty);
        $this->assertEquals('B54321', $accessory->order_number);
        $this->assertEquals(199.99, $accessory->purchase_cost);
        $this->assertEquals('XYZ123', $accessory->model_number);
        $this->assertEquals($categoryB->id, $accessory->category_id);
        $this->assertEquals($companyB->id, $accessory->company_id);
        $this->assertEquals($locationB->id, $accessory->location_id);
        $this->assertEquals($manufacturerB->id, $accessory->manufacturer_id);
        $this->assertEquals($supplierB->id, $accessory->supplier_id);
    }
}
