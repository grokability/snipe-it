<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class UpdateAccessoryTest extends TestCase
{
    public function test_requires_permission_to_see_edit_accessory_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('accessories.edit', Accessory::factory()->create()))
            ->assertForbidden();
    }

    public function test_edit_accessory_page_renders()
    {
        $this->actingAs(User::factory()->editAccessories()->create())
            ->get(route('accessories.edit', Accessory::factory()->create()->id))
            ->assertOk()
            ->assertViewIs('accessories.edit');
    }

    public function test_does_not_show_edit_accessory_page_from_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();
        $userForCompanyB = User::factory()->for($companyB)->editAccessories()->create();

        $this->actingAs($userForCompanyB)
            ->get(route('accessories.edit', $accessoryForCompanyA->id))
            ->assertRedirect(route('accessories.index'));
    }

    public function test_cannot_set_quantity_to_amount_lower_than_what_is_checked_out()
    {
        $accessory = Accessory::factory()->create(['qty' => 2]);
        $accessory->checkouts()->create(['assigned_to' => User::factory()->create()->id, 'qty' => 1]);
        $accessory->checkouts()->create(['assigned_to' => User::factory()->create()->id, 'qty' => 1]);

        $this->assertEquals(2, $accessory->checkouts->count());

        $this->actingAs(User::factory()->editAccessories()->create())
            ->put(route('accessories.update', $accessory), [
                'redirect_option' => 'index',
                'company_id' => (string) $accessory->company_id,
                'name' => $accessory->name,
                'category_id' => (string) $accessory->category_id,
                'supplier_id' => (string) $accessory->supplier_id,
                'manufacturer_id' => (string) $accessory->manufacturer_id,
                'location_id' => (string) $accessory->location_id,
                'model_number' => $accessory->model_number,
                'order_number' => $accessory->order_number,
                'purchase_date' => $accessory->purchase_date,
                'purchase_cost' => $accessory->purchase_cost,
                'min_amt' => $accessory->min_amt,
                'notes' => $accessory->notes,
                // the important part...
                // try to lower the qty to 1 when there are 2 checked out
                'qty' => '1',
            ]);
    }

    public function test_can_update_accessory()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        [$categoryA, $categoryB] = Category::factory()->count(2)->create();
        [$supplierA, $supplierB] = Supplier::factory()->count(2)->create();
        [$manufacturerA, $manufacturerB] = Manufacturer::factory()->count(2)->create();
        [$locationA, $locationB] = Location::factory()->count(2)->create();

        $accessory = Accessory::factory()
            ->for($companyA)
            ->for($categoryA)
            ->for($supplierA)
            ->for($manufacturerA)
            ->for($locationA)
            ->create([
                'min_amt' => 1,
                'qty' => 5,
            ]);

        $this->actingAs(User::factory()->editAccessories()->create())
            ->put(route('accessories.update', $accessory), [
                'redirect_option' => 'index',
                'company_id' => (string) $companyB->id,
                'name' => 'Changed Name',
                'category_id' => (string) $categoryB->id,
                'supplier_id' => (string) $supplierB->id,
                'manufacturer_id' => (string) $manufacturerB->id,
                'location_id' => (string) $locationB->id,
                'model_number' => 'changed 1234',
                'order_number' => 'changed 5678',
                'purchase_date' => '2024-10-11',
                'purchase_cost' => '83.52',
                'qty' => '7',
                'min_amt' => '10',
                'notes' => 'A new note',
            ])
            ->assertRedirect(route('accessories.index'));

        $this->assertDatabaseHas('accessories', [
            'company_id' => $companyB->id,
            'name' => 'Changed Name',
            'category_id' => $categoryB->id,
            'supplier_id' => $supplierB->id,
            'manufacturer_id' => $manufacturerB->id,
            'location_id' => $locationB->id,
            'model_number' => 'changed 1234',
            'order_number' => 'changed 5678',
            'purchase_date' => '2024-10-11',
            'purchase_cost' => '83.52',
            'qty' => '7',
            'min_amt' => '10',
            'notes' => 'A new note',
        ]);
    }

    public function test_update_logs_changed_fields_in_log_meta()
    {
        $accessory = Accessory::factory()->create([
            'qty' => 5,
            'name' => 'Old Name',
            'model_number' => null,
            'location_id' => null,
        ]);

        $this->actingAs(User::factory()->editAccessories()->create())
            ->put(route('accessories.update', $accessory), [
                'redirect_option' => 'index',
                'name' => 'New Name',
                'qty' => '10',
                'category_id' => (string) $accessory->category_id,
            ]);

        $log = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'update')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'No update log entry was created');
        $this->assertNotNull($log->log_meta, 'log_meta was not stored');

        $meta = json_decode($log->log_meta, true);
        $this->assertEquals('5', $meta['qty']['old']);
        $this->assertEquals('10', $meta['qty']['new']);
        $this->assertEquals('Old Name', $meta['name']['old']);
        $this->assertEquals('New Name', $meta['name']['new']);
    }

    public function test_no_op_update_does_not_create_log_entry()
    {
        $accessory = Accessory::factory()->create([
            'qty' => 5,
            'name' => 'Same Name',
            'model_number' => null,
            'location_id' => null,
        ]);

        $before = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'update')
            ->count();

        $this->actingAs(User::factory()->editAccessories()->create())
            ->put(route('accessories.update', $accessory), [
                'redirect_option' => 'index',
                'name' => 'Same Name',
                'qty' => '5',
                'category_id' => (string) $accessory->category_id,
            ]);

        $after = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'update')
            ->count();

        $this->assertEquals($before, $after, 'A spurious log entry was created for a no-op update');
    }
}
