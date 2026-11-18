<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createSuppliers()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('creates supplier with required fields', function () {
    $content = handle(['name' => 'Test Supplier'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('suppliers', ['name' => 'Test Supplier']);
});

test('response includes id and name', function () {
    $content = handle(['name' => 'Response Supplier'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Supplier');
});

test('returns error when name missing', function () {
    expect(handle([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle(['name' => 'Blocked Supplier'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('suppliers', ['name' => 'Blocked Supplier']);
});
