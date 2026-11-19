<?php

use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;

test('requires permission', function () {
    $supplier = Supplier::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.suppliers.destroy', $supplier))
        ->assertForbidden();

    $this->assertNotSoftDeleted($supplier);
});

test('cannot delete supplier with data still associated', function () {
    $supplierWithAsset = Supplier::factory()->hasAssets()->create();
    $supplierWithMaintenance = Supplier::factory()->has(Maintenance::factory(), 'maintenances')->create();
    $supplierWithLicense = Supplier::factory()->hasLicenses()->create();

    $actor = $this->actingAsForApi(User::factory()->deleteSuppliers()->create());

    $actor->deleteJson(route('api.suppliers.destroy', $supplierWithAsset))->assertStatusMessageIs('error');
    $actor->deleteJson(route('api.suppliers.destroy', $supplierWithMaintenance))->assertStatusMessageIs('error');
    $actor->deleteJson(route('api.suppliers.destroy', $supplierWithLicense))->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($supplierWithAsset);
    $this->assertNotSoftDeleted($supplierWithMaintenance);
    $this->assertNotSoftDeleted($supplierWithLicense);
});

test('can delete supplier', function () {
    $supplier = Supplier::factory()->create();

    $this->actingAsForApi(User::factory()->deleteSuppliers()->create())
        ->deleteJson(route('api.suppliers.destroy', $supplier))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($supplier);
});
