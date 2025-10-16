<?php

namespace Tests\Feature\Suppliers\Ui;

use App\Models\Asset;
use App\Models\Supplier;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class BulkDeleteSuppliersTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('suppliers.bulk.delete'), [
                'ids' => [1, 2, 3]
            ])
            ->assertForbidden();
    }

    public function test_suppliers_cannot_be_bulk_deleted_if_models_still_associated()
    {
        $supplier = Supplier::factory()->create();
        Asset::factory()->create(['supplier_id' => $supplier->id]);
        
        $this->actingAs(User::factory()->deleteSuppliers()->create())
            ->post(route('suppliers.bulk.delete'), [
                'ids' => [$supplier->id]
            ]);
            
        $this->assertModelExists($supplier);
        $this->assertNotSoftDeleted($supplier);
    }

    public function test_supplier_can_be_bulk_deleted()
    {
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();
        $supplier3 = Supplier::factory()->create();

        $this->actingAs(User::factory()->deleteSuppliers()->create())
            ->post(route('suppliers.bulk.delete'), [
                'ids' => [$supplier1->id, $supplier2->id, $supplier3->id]
            ])
            ->assertRedirect(route('suppliers.index'));

        $this->assertSoftDeleted($supplier1);
        $this->assertSoftDeleted($supplier2);
        $this->assertSoftDeleted($supplier3);
    }
}