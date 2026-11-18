<?php

use App\Mcp\Tools\ShowLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewLocations()->create());
});

function showLocationTool(array $args = []): ResponseFactory
{
    return (new ShowLocationTool)->handle(new Request($args));
}

test('returns location by id', function () {
    $location = Location::factory()->create();

    $content = showLocationTool(['id' => $location->id])->getStructuredContent();

    expect($content['id'])->toEqual($location->id);
    expect($content['name'])->toEqual($location->name);
});

test('returns location by name', function () {
    $location = Location::factory()->create();

    $content = showLocationTool(['name' => $location->name])->getStructuredContent();

    expect($content['id'])->toEqual($location->id);
    expect($content['name'])->toEqual($location->name);
});

test('returns error when no identifier provided', function () {
    expect(showLocationTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showLocationTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $location = Location::factory()->create();

    expect(showLocationTool(['id' => $location->id])->responses()->first()->isError())->toBeTrue();
});
