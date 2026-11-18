<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createManufacturers()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('creates manufacturer with required fields', function () {
    $content = handle(['name' => 'Test Manufacturer'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('manufacturers', ['name' => 'Test Manufacturer']);
});

test('response includes id and name', function () {
    $content = handle(['name' => 'Response Manufacturer'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Manufacturer');
});

test('returns error when name missing', function () {
    expect(handle([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle(['name' => 'Blocked Manufacturer'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('manufacturers', ['name' => 'Blocked Manufacturer']);
});
