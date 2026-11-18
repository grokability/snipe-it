<?php

use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteSuppliers()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes supplier by id', function () {
    $supplier = Supplier::factory()->create();

    $content = handle(['id' => $supplier->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('deletes supplier by name', function () {
    $supplier = Supplier::factory()->create(['name' => 'Delete By Name Supplier']);

    $content = handle(['name' => 'Delete By Name Supplier'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $supplier->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('suppliers', ['id' => $supplier->id]);
});
