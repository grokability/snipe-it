<?php

use App\Mcp\Tools\CreateConsumableTool;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createConsumables()->create());
});

function createConsumableTool(array $args = []): ResponseFactory
{
    return (new CreateConsumableTool)->handle(new Request($args));
}

test('creates consumable with required fields', function () {
    $category = Category::factory()->create(['category_type' => 'consumable']);

    $content = createConsumableTool([
        'name' => 'Test Toner Cartridge',
        'qty' => 10,
        'category_id' => $category->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('consumables', [
        'name' => 'Test Toner Cartridge',
        'qty' => 10,
        'category_id' => $category->id,
    ]);
});

test('response includes id name and qty', function () {
    $category = Category::factory()->create(['category_type' => 'consumable']);

    $content = createConsumableTool([
        'name' => 'Response Check Consumable',
        'qty' => 5,
        'category_id' => $category->id,
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Check Consumable');
    expect($content['qty'])->toEqual(5);
});

test('creates with optional fields', function () {
    $category = Category::factory()->create(['category_type' => 'consumable']);
    $location = Location::factory()->create();

    // Per-acquisition fields (order_number, purchase_date) moved to
    // the Orders table. purchase_cost aliases default_purchase_cost.
    createConsumableTool([
        'name' => 'Full Consumable',
        'qty' => 20,
        'category_id' => $category->id,
        'location_id' => $location->id,
        'purchase_cost' => 9.99,
        'min_amt' => 5,
        'notes' => 'Test consumable notes',
    ]);

    $this->assertDatabaseHas('consumables', [
        'name' => 'Full Consumable',
        'qty' => 20,
        'location_id' => $location->id,
        'default_purchase_cost' => 9.99,
        'min_amt' => 5,
    ]);
});

test('returns error when name missing', function () {
    $category = Category::factory()->create(['category_type' => 'consumable']);

    expect(createConsumableTool([
        'qty' => 5,
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when qty missing', function () {
    $category = Category::factory()->create(['category_type' => 'consumable']);

    expect(createConsumableTool([
        'name' => 'No Qty Consumable',
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category missing', function () {
    expect(createConsumableTool([
        'name' => 'No Category Consumable',
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create(['category_type' => 'consumable']);

    expect(createConsumableTool([
        'name' => 'Blocked Consumable',
        'qty' => 1,
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseMissing('consumables', ['name' => 'Blocked Consumable']);
});
