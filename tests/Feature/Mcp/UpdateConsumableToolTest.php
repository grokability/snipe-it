<?php

use App\Mcp\Tools\UpdateConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editConsumables()->create());
});

function updateConsumableTool(array $args = []): ResponseFactory
{
    return (new UpdateConsumableTool)->handle(new Request($args));
}

test('updates consumable by id', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $content = updateConsumableTool([
        'id' => $consumable->id,
        'qty' => 20,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['qty'])->toEqual(20);
    $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'qty' => 20]);
});

test('updates consumable by name', function () {
    $consumable = Consumable::factory()->create(['name' => 'Update By Name', 'qty' => 3]);

    $content = updateConsumableTool([
        'name' => 'Update By Name',
        'qty' => 15,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'qty' => 15]);
});

test('renames via new name', function () {
    $consumable = Consumable::factory()->create(['name' => 'Old Consumable Name']);

    $content = updateConsumableTool([
        'id' => $consumable->id,
        'new_name' => 'New Consumable Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('New Consumable Name');
    $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'name' => 'New Consumable Name']);
});

test('returns error when not found', function () {
    expect(updateConsumableTool(['id' => 999999, 'qty' => 5])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $consumable = Consumable::factory()->create(['qty' => 5]);

    expect(updateConsumableTool([
        'id' => $consumable->id,
        'qty' => 99,
    ])->responses()->first()->isError())->toBeTrue();
});
