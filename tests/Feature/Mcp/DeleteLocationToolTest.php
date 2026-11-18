<?php

use App\Mcp\Tools\DeleteLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteLocations()->create());
});

function deleteLocationTool(array $args = []): ResponseFactory
{
    return (new DeleteLocationTool)->handle(new Request($args));
}

test('deletes location by id', function () {
    $location = Location::factory()->create();

    $content = deleteLocationTool(['id' => $location->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('locations', ['id' => $location->id]);
});

test('deletes location by name', function () {
    $location = Location::factory()->create();

    $content = deleteLocationTool(['name' => $location->name])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('locations', ['id' => $location->id]);
});

test('returns error when not found', function () {
    expect(deleteLocationTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when location has users', function () {
    $location = Location::factory()->create();
    User::factory()->create(['location_id' => $location->id]);

    $response = deleteLocationTool(['id' => $location->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('locations', ['id' => $location->id]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $location = Location::factory()->create();

    expect(deleteLocationTool(['id' => $location->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('locations', ['id' => $location->id]);
});
