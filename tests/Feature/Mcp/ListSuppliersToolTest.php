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

test('returns suppliers with pagination', function () {
    Supplier::factory()->count(3)->create();

    $content = handle()->getStructuredContent();

    expect($content)->toHaveKey('suppliers');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each supplier has key fields', function () {
    Supplier::factory()->create(['name' => 'Key Fields Supplier']);

    $content = handle(['search' => 'Key Fields Supplier'])->getStructuredContent();

    expect($content['suppliers'])->not->toBeEmpty();
    $supplier = $content['suppliers'][0];
    expect($supplier)->toHaveKey('id');
    expect($supplier)->toHaveKey('name');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle()->responses()->first()->isError())->toBeTrue();
});
