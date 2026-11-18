<?php

use App\Mcp\Tools\CreateLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createLocations()->create());
});

function createLocationTool(array $args = []): ResponseFactory
{
    return (new CreateLocationTool)->handle(new Request($args));
}

test('creates location with required fields', function () {
    $content = createLocationTool(['name' => 'Test Location'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('locations', ['name' => 'Test Location']);
});

test('response includes id and name', function () {
    $content = createLocationTool(['name' => 'Location With ID'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Location With ID');
});

test('creates child location', function () {
    $parent = Location::factory()->create();

    $content = createLocationTool([
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
    expect(createLocationTool([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createLocationTool(['name' => 'Blocked Location'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('locations', ['name' => 'Blocked Location']);
});
