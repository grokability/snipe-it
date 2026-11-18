<?php

use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewManufacturers()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns manufacturers with pagination', function () {
    Manufacturer::factory()->count(3)->create();

    $content = handle()->getStructuredContent();

    expect($content)->toHaveKey('manufacturers');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each manufacturer has key fields', function () {
    Manufacturer::factory()->create(['name' => 'Key Fields Manufacturer']);

    $content = handle(['search' => 'Key Fields Manufacturer'])->getStructuredContent();

    expect($content['manufacturers'])->not->toBeEmpty();
    $manufacturer = $content['manufacturers'][0];
    expect($manufacturer)->toHaveKey('id');
    expect($manufacturer)->toHaveKey('name');
});

test('limit controls results', function () {
    Manufacturer::factory()->count(5)->create();

    $content = handle(['limit' => 2])->getStructuredContent();

    expect($content['manufacturers'])->toHaveCount(2);
    expect($content['limit'])->toEqual(2);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle()->responses()->first()->isError())->toBeTrue();
});
