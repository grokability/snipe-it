<?php

use App\Mcp\Servers\SnipeMCPServer;
use App\Mcp\Tools\ListConsumablesTool;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewConsumables()->create());
});

function listConsumablesTool(array $args = []): ResponseFactory
{
    return (new ListConsumablesTool)->handle(new Request($args));
}

test('returns total and pagination metadata', function () {
    Consumable::factory()->count(3)->create();

    $content = listConsumablesTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('offset');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('consumables');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each consumable includes key fields', function () {
    Consumable::factory()->create(['name' => 'Test Ink Cartridge']);

    $content = listConsumablesTool(['limit' => 500])->getStructuredContent();

    $consumable = collect($content['consumables'])->firstWhere('name', 'Test Ink Cartridge');

    expect($consumable)->not->toBeNull();
    expect($consumable)->toHaveKey('id');
    expect($consumable)->toHaveKey('name');
    expect($consumable)->toHaveKey('qty');
    expect($consumable)->toHaveKey('category');
});

test('limit controls results', function () {
    Consumable::factory()->count(10)->create();

    $content = listConsumablesTool(['limit' => 3])->getStructuredContent();

    expect($content['consumables'])->toHaveCount(3);
    expect($content['limit'])->toEqual(3);
});

test('filters by category id', function () {
    $category = Category::factory()->create(['category_type' => 'consumable']);
    Consumable::factory()->create(['name' => 'Category Match', 'category_id' => $category->id]);
    Consumable::factory()->create(['name' => 'Other Consumable']);

    $content = listConsumablesTool(['category_id' => $category->id])->getStructuredContent();

    expect($content['total'])->toEqual(1);
    expect($content['consumables'][0]['name'])->toEqual('Category Match');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listConsumablesTool()->responses()->first()->isError())->toBeTrue();
});

test('low stock only returns only rows at or below min amt', function () {
    $healthy = Consumable::factory()->create(['name' => 'Plenty', 'qty' => 100, 'min_amt' => 10]);
    $low = Consumable::factory()->create(['name' => 'Low', 'qty' => 2, 'min_amt' => 5]);

    $content = listConsumablesTool(['low_stock_only' => true])->getStructuredContent();
    $names = array_column($content['consumables'], 'name');

    expect($names)->toContain('Low');
    expect($names)->not->toContain('Plenty');
});

test('filter payload forwards to text search', function () {
    $match = Consumable::factory()->create(['name' => 'Toner Cartridge Cyan']);
    Consumable::factory()->create(['name' => 'Unrelated Item']);

    $content = listConsumablesTool(['filter' => ['name' => 'Toner']])->getStructuredContent();
    $names = array_column($content['consumables'], 'name');

    expect($names)->toContain('Toner Cartridge Cyan');
});

test('response includes qty min amt remaining and notes', function () {
    Consumable::factory()->create(['name' => 'Response Field Check', 'qty' => 50, 'min_amt' => 5]);

    $content = listConsumablesTool(['limit' => 500])->getStructuredContent();
    $row = collect($content['consumables'])->firstWhere('name', 'Response Field Check');

    expect($row)->not->toBeNull();
    expect($row['qty'])->toBe(50);
    expect($row['min_amt'])->toBe(5);
    expect($row)->toHaveKey('remaining');
    expect($row)->toHaveKey('supplier');
    expect($row)->toHaveKey('notes');
});

test('default hides archived consumables', function () {
    Consumable::factory()->create(['name' => 'Active Consumable']);
    Consumable::factory()->archived()->create(['name' => 'Retired Consumable']);

    $content = listConsumablesTool()->getStructuredContent();

    $names = array_column($content['consumables'], 'name');
    expect($names)->toContain('Active Consumable');
    expect($names)->not->toContain('Retired Consumable');
});

test('include archived returns both', function () {
    Consumable::factory()->create(['name' => 'Active Consumable']);
    Consumable::factory()->archived()->create(['name' => 'Retired Consumable']);

    $content = listConsumablesTool(['include_archived' => true])->getStructuredContent();

    $names = array_column($content['consumables'], 'name');
    expect($names)->toContain('Active Consumable');
    expect($names)->toContain('Retired Consumable');
});

test('archived only returns archived rows only', function () {
    Consumable::factory()->create(['name' => 'Active Consumable']);
    Consumable::factory()->archived()->create(['name' => 'Retired Consumable']);

    $content = listConsumablesTool(['archived_only' => true])->getStructuredContent();

    $names = array_column($content['consumables'], 'name');
    expect($names)->not->toContain('Active Consumable');
    expect($names)->toContain('Retired Consumable');
});

test('each row includes archived flag', function () {
    Consumable::factory()->archived()->create(['name' => 'Retired']);

    $content = listConsumablesTool(['archived_only' => true])->getStructuredContent();

    $row = collect($content['consumables'])->firstWhere('name', 'Retired');
    expect($row)->not->toBeNull();
    expect($row)->toHaveKey('archived');
    expect($row['archived'])->toBeTrue();
});

test('low stock excludes archived even when include archived true', function () {
    Consumable::factory()->archived()->create([
        'name' => 'Archived Low Stock',
        'qty' => 1,
        'min_amt' => 5,
    ]);

    $content = listConsumablesTool([
        'low_stock_only' => true,
        'include_archived' => true,
    ])->getStructuredContent();

    $names = array_column($content['consumables'], 'name');
    expect($names)->not->toContain('Archived Low Stock');
});

test('filters by company name', function () {
    $company = Company::factory()->create(['name' => 'Consumable Company']);
    $matching = Consumable::factory()->create(['company_id' => $company->id]);
    Consumable::factory()->create();

    $content = listConsumablesTool(['company_name' => $company->name])->getStructuredContent();

    expect($content['total'])->toBe(1);
    expect(array_column($content['consumables'], 'id'))->toBe([$matching->id]);
});

test('returns error for an unknown company name', function () {
    config(['app.debug' => false]);

    $response = SnipeMCPServer::tool(ListConsumablesTool::class, [
        'company_name' => 'Unknown Consumable Company',
    ]);

    $response->assertHasErrors([
        trans('mcp.fk_not_found', [
            'field' => 'company_name',
            'value' => 'Unknown Consumable Company',
        ]),
    ]);
});
