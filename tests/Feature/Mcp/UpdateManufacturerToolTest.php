<?php

use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editManufacturers()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('updates manufacturer by id', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Original Manufacturer']);

    $content = handle([
        'id' => $manufacturer->id,
        'new_name' => 'Updated Manufacturer',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('manufacturers', ['id' => $manufacturer->id, 'name' => 'Updated Manufacturer']);
});

test('renames via new name', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Old Manufacturer Name']);

    $content = handle([
        'name' => 'Old Manufacturer Name',
        'new_name' => 'New Manufacturer Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('New Manufacturer Name');
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Unchanged Manufacturer']);
    $this->actingAs(User::factory()->create());

    expect(handle([
        'id' => $manufacturer->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('manufacturers', ['id' => $manufacturer->id, 'name' => 'Unchanged Manufacturer']);
});
