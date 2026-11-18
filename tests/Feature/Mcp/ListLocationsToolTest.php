<?php

use App\Mcp\Tools\ListLocationsTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewLocations()->create());
});

function listLocationsTool(array $args = []): ResponseFactory
{
    return (new ListLocationsTool)->handle(new Request($args));
}

test('returns locations with pagination', function () {
    Location::factory()->count(3)->create();

    $content = listLocationsTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('locations');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each location has key fields', function () {
    Location::factory()->create();

    $content = listLocationsTool()->getStructuredContent();

    expect($content['locations'])->not->toBeEmpty();
    $first = $content['locations'][0];
    expect($first)->toHaveKey('id');
    expect($first)->toHaveKey('name');
});

test('filters by parent id', function () {
    $parent = Location::factory()->create();
    $child = Location::factory()->create(['parent_id' => $parent->id]);
    Location::factory()->create();

    // another location without parent
    $content = listLocationsTool(['parent_id' => $parent->id])->getStructuredContent();

    $ids = array_column($content['locations'], 'id');
    expect($ids)->toContain($child->id);
    expect($ids)->not->toContain($parent->id);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listLocationsTool()->responses()->first()->isError())->toBeTrue();
});
