<?php

use App\Mcp\Tools\UpdateSupplierTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editSuppliers()->create());
});

function updateSupplierTool(array $args = []): ResponseFactory
{
    return (new UpdateSupplierTool)->handle(new Request($args));
}

test('updates supplier by id', function () {
    $supplier = Supplier::factory()->create(['name' => 'Original Supplier']);

    $content = updateSupplierTool([
        'id' => $supplier->id,
        'new_name' => 'Updated Supplier',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated Supplier']);
});

test('renames via new name', function () {
    $supplier = Supplier::factory()->create(['name' => 'Old Supplier Name']);

    $content = updateSupplierTool([
        'name' => 'Old Supplier Name',
        'new_name' => 'New Supplier Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('New Supplier Name');
});

test('returns error when not found', function () {
    expect(updateSupplierTool(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $supplier = Supplier::factory()->create(['name' => 'Unchanged Supplier']);
    $this->actingAs(User::factory()->create());

    expect(updateSupplierTool([
        'id' => $supplier->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Unchanged Supplier']);
});
