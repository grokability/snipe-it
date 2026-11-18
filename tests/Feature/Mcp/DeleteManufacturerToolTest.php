<?php

use App\Mcp\Tools\DeleteManufacturerTool;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteManufacturers()->create());
});

function deleteManufacturerTool(array $args = []): ResponseFactory
{
    return (new DeleteManufacturerTool)->handle(new Request($args));
}

test('deletes manufacturer by id', function () {
    $manufacturer = Manufacturer::factory()->create();

    $content = deleteManufacturerTool(['id' => $manufacturer->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('manufacturers', ['id' => $manufacturer->id]);
});

test('deletes manufacturer by name', function () {
    $manufacturer = Manufacturer::factory()->create(['name' => 'Delete By Name Manufacturer']);

    $content = deleteManufacturerTool(['name' => 'Delete By Name Manufacturer'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('manufacturers', ['id' => $manufacturer->id]);
});

test('returns error when not found', function () {
    expect(deleteManufacturerTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $manufacturer = Manufacturer::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(deleteManufacturerTool(['id' => $manufacturer->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('manufacturers', ['id' => $manufacturer->id]);
});
