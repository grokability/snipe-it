<?php

use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editLocations()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('updates location by id', function () {
    $location = Location::factory()->create();

    $content = handle([
        'id' => $location->id,
        'city' => 'New City',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'city' => 'New City',
    ]);
});

test('renames via new name', function () {
    $location = Location::factory()->create();

    $content = handle([
        'id' => $location->id,
        'new_name' => 'Renamed Location',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('Renamed Location');
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'name' => 'Renamed Location',
    ]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $location = Location::factory()->create();

    expect(handle(['id' => $location->id, 'city' => 'Hacked'])->responses()->first()->isError())->toBeTrue();
});
