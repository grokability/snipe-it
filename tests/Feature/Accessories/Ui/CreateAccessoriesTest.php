<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class CreateAccessoriesTest extends TestCase
{
    public function test_requires_permission_to_view_create_accessory_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('accessories.create'))
            ->assertForbidden();
    }

    public function test_create_accessory_page_renders()
    {
        $this->actingAs(User::factory()->createAccessories()->create())
            ->get(route('accessories.create'))
            ->assertOk()
            ->assertViewIs('accessories.edit');
    }

    public function test_requires_permission_to_create_accessory()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('accessories.store'))
            ->assertForbidden();
    }

    public function test_valid_data_required_to_create_accessory()
    {
        $this->actingAs(User::factory()->createAccessories()->create())
            ->post(route('accessories.store'), [
                //
            ])
            ->assertSessionHasErrors([
                'name',
                'category_id',
            ]);
    }

    public function test_can_create_accessory()
    {
        $category = Category::factory()->create();
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'category_id' => $category->id,
            'company_id' => $company->id,
            'location_id' => $location->id,
            'manufacturer_id' => $manufacturer->id,
            'min_amt' => '1',
            'model_number' => '12345',
            'name' => 'My Accessory Name',
            'notes' => 'Some notes here',
            'order_number' => '9876',
            'purchase_cost' => '99.98',
            'purchase_date' => '2024-09-04',
            'qty' => '3',
            'supplier_id' => $supplier->id,
        ];

        $user = User::factory()->createAccessories()->create();

        $this->actingAs($user)
            ->post(route('accessories.store'), array_merge($data, ['redirect_option' => 'index']))
            ->assertRedirect(route('accessories.index'));

        $this->assertDatabaseHas('accessories', array_merge($data, ['created_by' => $user->id]));
    }
}
