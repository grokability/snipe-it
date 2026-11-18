<?php

use App\Mcp\Tools\ShowConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewConsumables()->create());
});

function showConsumableTool(array $args = []): ResponseFactory
{
    return (new ShowConsumableTool)->handle(new Request($args));
}

test('returns consumable by id', function () {
    $consumable = Consumable::factory()->create(['name' => 'Find By ID Consumable']);

    $content = showConsumableTool(['id' => $consumable->id])->getStructuredContent();

    expect($content['id'])->toEqual($consumable->id);
    expect($content['name'])->toEqual('Find By ID Consumable');
});

test('returns consumable by name', function () {
    Consumable::factory()->create(['name' => 'Find By Name Consumable']);

    $content = showConsumableTool(['name' => 'Find By Name Consumable'])->getStructuredContent();

    expect($content['name'])->toEqual('Find By Name Consumable');
});

test('response includes qty and users count', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $content = showConsumableTool(['id' => $consumable->id])->getStructuredContent();

    expect($content['qty'])->toEqual(5);
    expect($content)->toHaveKey('users_count');
    expect($content['users_count'])->toEqual(0);
});

test('returns error when no identifier provided', function () {
    expect(showConsumableTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showConsumableTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $consumable = Consumable::factory()->create();

    expect(showConsumableTool(['id' => $consumable->id])->responses()->first()->isError())->toBeTrue();
});
