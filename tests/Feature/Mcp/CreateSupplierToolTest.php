<?php

use App\Mcp\Tools\CreateSupplierTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createSuppliers()->create());
});

function createSupplierTool(array $args = []): ResponseFactory
{
    return (new CreateSupplierTool)->handle(new Request($args));
}

test('creates supplier with required fields', function () {
    $content = createSupplierTool(['name' => 'Test Supplier'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('suppliers', ['name' => 'Test Supplier']);
});

test('response includes id and name', function () {
    $content = createSupplierTool(['name' => 'Response Supplier'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Supplier');
});

test('returns error when name missing', function () {
    expect(createSupplierTool([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createSupplierTool(['name' => 'Blocked Supplier'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('suppliers', ['name' => 'Blocked Supplier']);
});
