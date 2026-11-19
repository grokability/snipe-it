<?php

use App\Models\Asset;
use App\Models\Supplier;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('suppliers.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('suppliers cannot be bulk deleted if models still associated', function () {
    $supplier = Supplier::factory()->create();
    Asset::factory()->create(['supplier_id' => $supplier->id]);

    $this->actingAs(User::factory()->deleteSuppliers()->create())
        ->post(route('suppliers.bulk.delete'), [
            'ids' => [$supplier->id],
        ]);

    $this->assertModelExists($supplier);
    $this->assertNotSoftDeleted($supplier);
});

test('supplier can be bulk deleted', function () {
    $supplier1 = Supplier::factory()->create();
    $supplier2 = Supplier::factory()->create();
    $supplier3 = Supplier::factory()->create();

    $this->actingAs(User::factory()->deleteSuppliers()->create())
        ->post(route('suppliers.bulk.delete'), [
            'ids' => [$supplier1->id, $supplier2->id, $supplier3->id],
        ])
        ->assertRedirect(route('suppliers.index'));

    $this->assertSoftDeleted($supplier1);
    $this->assertSoftDeleted($supplier2);
    $this->assertSoftDeleted($supplier3);
});

test('bulk success message pluralizes by count', function () {
    $solo = Supplier::factory()->create();

    $this->actingAs(User::factory()->deleteSuppliers()->create())
        ->post(route('suppliers.bulk.delete'), [
            'ids' => [$solo->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/suppliers/message.delete.bulk_success', 1, ['count' => 1]));

    $a = Supplier::factory()->create();
    $b = Supplier::factory()->create();
    $c = Supplier::factory()->create();

    $this->actingAs(User::factory()->deleteSuppliers()->create())
        ->post(route('suppliers.bulk.delete'), [
            'ids' => [$a->id, $b->id, $c->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/suppliers/message.delete.bulk_success', 3, ['count' => 3]));
});
