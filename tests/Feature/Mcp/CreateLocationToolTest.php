<?php

use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createLocations()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('creates location with required fields', function () {
    $content = handle(['name' => 'Test Location'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('locations', ['name' => 'Test Location']);
});

test('response includes id and name', function () {
    $content = handle(['name' => 'Location With ID'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Location With ID');
});

test('creates child location', function () {
    $parent = Location::factory()->create();

    $content = handle([
        'name' => 'Child Location',
        'parent_id' => $parent->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('locations', [
        'name' => 'Child Location',
        'parent_id' => $parent->id,
    ]);
});

test('returns error when name missing', function () {
    expect(handle([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle(['name' => 'Blocked Location'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('locations', ['name' => 'Blocked Location']);
});
