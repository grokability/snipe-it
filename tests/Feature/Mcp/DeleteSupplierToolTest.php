<?php

use App\Mcp\Tools\DeleteSupplierTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteSuppliers()->create());
});

function deleteSupplierTool(array $args = []): ResponseFactory
{
    return (new DeleteSupplierTool)->handle(new Request($args));
}

test('deletes supplier by id', function () {
    $supplier = Supplier::factory()->create();

    $content = deleteSupplierTool(['id' => $supplier->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('deletes supplier by name', function () {
    $supplier = Supplier::factory()->create(['name' => 'Delete By Name Supplier']);

    $content = deleteSupplierTool(['name' => 'Delete By Name Supplier'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('returns error when not found', function () {
    expect(deleteSupplierTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(deleteSupplierTool(['id' => $supplier->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('suppliers', ['id' => $supplier->id]);
});
