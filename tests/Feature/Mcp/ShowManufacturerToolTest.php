<?php

use App\Mcp\Tools\ShowManufacturerTool;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewManufacturers()->create());
});

function showManufacturerTool(array $args = []): ResponseFactory
{
    return (new ShowManufacturerTool)->handle(new Request($args));
}

test('returns manufacturer by id', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Show By ID Manufacturer']);

    $content = showManufacturerTool(['id' => $manufacturer->id])->getStructuredContent();

    expect($content['id'])->toEqual($manufacturer->id);
    expect($content['name'])->toEqual('Show By ID Manufacturer');
});

test('returns manufacturer by name', function () {
    Manufacturer::factory()->create(['name' => 'Show By Name Manufacturer']);

    $content = showManufacturerTool(['name' => 'Show By Name Manufacturer'])->getStructuredContent();

    expect($content['name'])->toEqual('Show By Name Manufacturer');
});

test('returns error when no identifier provided', function () {
    expect(showManufacturerTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showManufacturerTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $manufacturer = Manufacturer::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(showManufacturerTool(['id' => $manufacturer->id])->responses()->first()->isError())->toBeTrue();
});
