<?php

use App\Mcp\Tools\CreateAccessoryTool;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createAccessories()->create());
});

function createAccessoryTool(array $args): ResponseFactory
{
    return (new CreateAccessoryTool)->handle(new Request($args));
}

test('creates accessory with required fields', function () {
    $category = Category::factory()->forAccessories()->create();

    $content = createAccessoryTool([
        'name' => 'Test Keyboard',
        'category_id' => $category->id,
        'qty' => 5,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories', ['name' => 'Test Keyboard', 'qty' => 5]);
});

test('response includes id name and qty', function () {
    $category = Category::factory()->forAccessories()->create();

    $content = createAccessoryTool([
        'name' => 'Response Check Accessory',
        'category_id' => $category->id,
        'qty' => 3,
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Check Accessory');
    expect($content['qty'])->toEqual(3);
});

test('creates accessory with optional fields', function () {
    // Per-acquisition data (order_number, purchase_date) now lives
    // on the Orders table rather than the Accessory row, so this
    // test only covers the fields that still land on accessories.
    // supplier_id / purchase_cost are accepted as input aliases
    // that map to default_supplier_id / default_purchase_cost.
    $category = Category::factory()->forAccessories()->create();
    $location = Location::factory()->create();

    createAccessoryTool([
        'name' => 'Full Accessory',
        'category_id' => $category->id,
        'qty' => 10,
        'model_number' => 'MN-1234',
        'location_id' => $location->id,
        'purchase_cost' => 29.99,
        'min_amt' => 2,
        'notes' => 'Test notes',
    ]);

    $this->assertDatabaseHas('accessories', [
        'name' => 'Full Accessory',
        'model_number' => 'MN-1234',
        'location_id' => $location->id,
        'default_purchase_cost' => 29.99,
        'min_amt' => 2,
    ]);
});

test('returns error when name missing', function () {
    $category = Category::factory()->forAccessories()->create();

    expect(createAccessoryTool([
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category missing', function () {
    expect(createAccessoryTool([
        'name' => 'No Category',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category does not exist', function () {
    expect(createAccessoryTool([
        'name' => 'Bad Category',
        'category_id' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create(['category_type' => 'accessory']);

    expect(createAccessoryTool([
        'name' => 'Blocked Accessory',
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});
