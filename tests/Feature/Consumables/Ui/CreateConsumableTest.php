<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class CreateConsumableTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('consumables.create'))
            ->assertForbidden();
    }

    public function testCanRenderCreateConsumablePage()
    {
        $this->actingAs(User::factory()->createConsumables()->create())
            ->get(route('consumables.create'))
            ->assertOk()
            ->assertViewIs('consumables.edit');
    }

    public function testCanCreateConsumable()
    {
        $data = [
            'company_id' => Company::factory()->create()->id,
            'name' => 'My Consumable',
            'category_id' => Category::factory()->consumableInkCategory()->create()->id,
            'supplier_id' => Supplier::factory()->create()->id,
            'manufacturer_id' => Manufacturer::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'model_number' => '1234',
            'item_no' => '5678',
            'order_number' => '908',
            'purchase_date' => '2024-12-05',
            'purchase_cost' => '89.45',
            'qty' => '10',
            'min_amt' => '1',
            'notes' => 'Some Notes',
        ];

        $this->actingAs(User::factory()->createConsumables()->create())
            ->post(route('consumables.store'), $data + [
                    'redirect_option' => 'index',
                    'category_type' => 'consumable',
                ])
            ->assertRedirect(route('consumables.index'));

        $this->assertDatabaseHas('consumables', $data);
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.create'))
            ->assertOk();

    }
}
