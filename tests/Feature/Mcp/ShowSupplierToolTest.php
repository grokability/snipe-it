<?php

use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewSuppliers()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns supplier by id', function () {
    $supplier = Supplier::factory()->create(['name' => 'Show By ID Supplier']);

    $content = handle(['id' => $supplier->id])->getStructuredContent();

    expect($content['id'])->toEqual($supplier->id);
    expect($content['name'])->toEqual('Show By ID Supplier');
});

test('returns supplier by name', function () {
    Supplier::factory()->create(['name' => 'Show By Name Supplier']);

    $content = handle(['name' => 'Show By Name Supplier'])->getStructuredContent();

    expect($content['name'])->toEqual('Show By Name Supplier');
});

test('returns error when no identifier provided', function () {
    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $supplier->id])->responses()->first()->isError())->toBeTrue();
});
