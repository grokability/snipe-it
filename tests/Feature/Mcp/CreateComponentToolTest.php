<?php

use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createComponents()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('creates component with required fields', function () {
    $category = Category::factory()->forComponents()->create();

    $content = handle([
        'name' => 'Test RAM',
        'category_id' => $category->id,
        'qty' => 10,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components', ['name' => 'Test RAM', 'qty' => 10]);
});

test('response includes id name qty and category', function () {
    $category = Category::factory()->forComponents()->create();

    $content = handle([
        'name' => 'Response Check Component',
        'category_id' => $category->id,
        'qty' => 5,
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Check Component');
    expect($content['qty'])->toEqual(5);
    expect($content['category_id'])->toEqual($category->id);
});

test('creates component with optional fields', function () {
    $category = Category::factory()->forComponents()->create();
    $location = Location::factory()->create();

    // Per-acquisition fields (order_number, purchase_date) moved to
    // the Orders table and no longer land on the components row.
    // purchase_cost is accepted as an input alias mapping to
    // default_purchase_cost on the parent.
    handle([
        'name' => 'Full Component',
        'category_id' => $category->id,
        'qty' => 20,
        'serial' => 'SN-COMP-001',
        'model_number' => 'MN-5678',
        'location_id' => $location->id,
        'purchase_cost' => 49.99,
        'min_amt' => 3,
        'notes' => 'Component notes',
    ]);

    $this->assertDatabaseHas('components', [
        'name' => 'Full Component',
        'serial' => 'SN-COMP-001',
        'model_number' => 'MN-5678',
        'location_id' => $location->id,
        'default_purchase_cost' => 49.99,
        'min_amt' => 3,
    ]);
});

test('returns error when name missing', function () {
    $category = Category::factory()->forComponents()->create();

    expect(handle([
        'category_id' => $category->id,
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category missing', function () {
    expect(handle([
        'name' => 'No Category',
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when qty missing', function () {
    $category = Category::factory()->forComponents()->create();

    expect(handle([
        'name' => 'No Qty',
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category does not exist', function () {
    expect(handle([
        'name' => 'Bad Category',
        'category_id' => 999999,
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create(['category_type' => 'component']);

    expect(handle([
        'name' => 'Blocked Component',
        'category_id' => $category->id,
        'qty' => 1,
    ])->responses()->first()->isError())->toBeTrue();
});
