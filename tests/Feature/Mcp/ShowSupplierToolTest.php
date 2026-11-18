<?php

use App\Mcp\Tools\ShowSupplierTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewSuppliers()->create());
});

function showSupplierTool(array $args = []): ResponseFactory
{
    return (new ShowSupplierTool)->handle(new Request($args));
}

test('returns supplier by id', function () {
    $supplier = Supplier::factory()->create(['name' => 'Show By ID Supplier']);

    $content = showSupplierTool(['id' => $supplier->id])->getStructuredContent();

    expect($content['id'])->toEqual($supplier->id);
    expect($content['name'])->toEqual('Show By ID Supplier');
});

test('returns supplier by name', function () {
    Supplier::factory()->create(['name' => 'Show By Name Supplier']);

    $content = showSupplierTool(['name' => 'Show By Name Supplier'])->getStructuredContent();

    expect($content['name'])->toEqual('Show By Name Supplier');
});

test('returns error when no identifier provided', function () {
    expect(showSupplierTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showSupplierTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(showSupplierTool(['id' => $supplier->id])->responses()->first()->isError())->toBeTrue();
});
