<?php

use App\Mcp\Tools\ListAccessoriesTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAccessories()->create());
});

function listAccessoriesTool(array $args = []): ResponseFactory
{
    return (new ListAccessoriesTool)->handle(new Request($args));
}

test('returns total and pagination metadata', function () {
    Accessory::factory()->count(3)->create();

    $content = listAccessoriesTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('offset');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('accessories');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each accessory includes qty min amt and remaining', function () {
    Accessory::factory()->create(['name' => 'Test Mouse', 'qty' => 10, 'min_amt' => 2]);

    $content = listAccessoriesTool(['limit' => 500])->getStructuredContent();

    $row = collect($content['accessories'])->firstWhere('name', 'Test Mouse');

    expect($row)->not->toBeNull();
    expect($row['qty'])->toBe(10);
    expect($row['min_amt'])->toBe(2);
    expect($row)->toHaveKey('remaining');
    expect($row)->toHaveKey('supplier');
    expect($row)->toHaveKey('notes');
});

test('filters by category id', function () {
    $category = Category::factory()->create(['category_type' => 'accessory']);
    Accessory::factory()->create(['name' => 'Category Match', 'category_id' => $category->id]);
    Accessory::factory()->create(['name' => 'Other Accessory']);

    $content = listAccessoriesTool(['category_id' => $category->id])->getStructuredContent();

    expect($content['total'])->toBe(1);
    expect($content['accessories'][0]['name'])->toBe('Category Match');
});

test('low stock only returns only rows at or below min amt', function () {
    $healthy = Accessory::factory()->create([
        'name' => 'Plenty In Stock',
        'qty' => 100,
        'min_amt' => 10,
    ]);
    $low = Accessory::factory()->create([
        'name' => 'Low Stock',
        'qty' => 3,
        'min_amt' => 5,
    ]);

    $content = listAccessoriesTool(['low_stock_only' => true])->getStructuredContent();

    $names = array_column($content['accessories'], 'name');
    expect($names)->toContain('Low Stock');
    expect($names)->not->toContain('Plenty In Stock');
});

test('low stock only accounts for active checkouts', function () {
    $accessory = Accessory::factory()->create([
        'name' => 'Checked Out Low',
        'qty' => 5,
        'min_amt' => 2,
    ]);

    // Check out 4 units. Remaining (5-4=1) is below min_amt (2) so it
    // should be reported as low stock even though raw qty > min_amt.
    AccessoryCheckout::factory()->count(4)->create([
        'accessory_id' => $accessory->id,
        'assigned_type' => User::class,
        'assigned_to' => User::factory()->create()->id,
    ]);

    $content = listAccessoriesTool(['low_stock_only' => true])->getStructuredContent();

    $names = array_column($content['accessories'], 'name');
    expect($names)->toContain('Checked Out Low');
});

test('default hides archived accessories', function () {
    Accessory::factory()->create(['name' => 'Active Accessory']);
    Accessory::factory()->archived()->create(['name' => 'Retired Accessory']);

    $content = listAccessoriesTool()->getStructuredContent();

    $names = array_column($content['accessories'], 'name');
    expect($names)->toContain('Active Accessory');
    expect($names)->not->toContain('Retired Accessory');
});

test('include archived returns both', function () {
    Accessory::factory()->create(['name' => 'Active Accessory']);
    Accessory::factory()->archived()->create(['name' => 'Retired Accessory']);

    $content = listAccessoriesTool(['include_archived' => true])->getStructuredContent();

    $names = array_column($content['accessories'], 'name');
    expect($names)->toContain('Active Accessory');
    expect($names)->toContain('Retired Accessory');
});

test('archived only returns archived rows only', function () {
    Accessory::factory()->create(['name' => 'Active Accessory']);
    Accessory::factory()->archived()->create(['name' => 'Retired Accessory']);

    $content = listAccessoriesTool(['archived_only' => true])->getStructuredContent();

    $names = array_column($content['accessories'], 'name');
    expect($names)->not->toContain('Active Accessory');
    expect($names)->toContain('Retired Accessory');
});

test('each accessory row includes archived flag', function () {
    Accessory::factory()->archived()->create(['name' => 'Retired']);

    $content = listAccessoriesTool(['archived_only' => true])->getStructuredContent();

    $row = collect($content['accessories'])->firstWhere('name', 'Retired');
    expect($row)->not->toBeNull();
    expect($row)->toHaveKey('archived');
    expect($row['archived'])->toBeTrue();
});

test('low stock excludes archived even when include archived true', function () {
    Accessory::factory()->archived()->create([
        'name' => 'Archived Low Stock',
        'qty' => 1,
        'min_amt' => 5,
    ]);

    $content = listAccessoriesTool([
        'low_stock_only' => true,
        'include_archived' => true,
    ])->getStructuredContent();

    $names = array_column($content['accessories'], 'name');
    expect($names)->not->toContain('Archived Low Stock');
});

test('limit controls results', function () {
    Accessory::factory()->count(5)->create();

    $content = listAccessoriesTool(['limit' => 2])->getStructuredContent();

    expect($content['accessories'])->toHaveCount(2);
    expect($content['limit'])->toBe(2);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listAccessoriesTool()->responses()->first()->isError())->toBeTrue();
});
