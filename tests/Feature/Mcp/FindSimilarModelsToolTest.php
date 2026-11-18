<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssetModels()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('returns error when no identifier provided', function () {
    expect(handle([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when model not found', function () {
    expect(handle(['model_id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('same category scores higher than same manufacturer alone', function () {
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $manufacturer = Manufacturer::factory()->create();

    $source = AssetModel::factory()->create([
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Source Model',
    ]);
    $sameCategory = AssetModel::factory()->create([
        'category_id' => $category->id,
        'manufacturer_id' => Manufacturer::factory()->create()->id,
        'name' => 'Different Manufacturer Same Category',
    ]);
    $sameManufacturer = AssetModel::factory()->create([
        'category_id' => $otherCategory->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Different Category Same Manufacturer',
    ]);

    $content = handle(['model_id' => $source->id])->getStructuredContent();

    $sameCategoryRow = collect($content['candidates'])->firstWhere('id', $sameCategory->id);
    $sameManufacturerRow = collect($content['candidates'])->firstWhere('id', $sameManufacturer->id);

    expect($sameCategoryRow['score'])->toBeGreaterThan($sameManufacturerRow['score']);
});

test('name token overlap contributes to score', function () {
    $category = Category::factory()->create();

    $source = AssetModel::factory()->create([
        'category_id' => $category->id,
        'name' => 'MacBook Pro 14-inch M3',
    ]);
    $overlap = AssetModel::factory()->create([
        'category_id' => $category->id,
        'name' => 'MacBook Pro 16-inch M3',
    ]);
    $noOverlap = AssetModel::factory()->create([
        'category_id' => $category->id,
        'name' => 'Something Completely Unrelated',
    ]);

    $content = handle(['model_id' => $source->id])->getStructuredContent();

    $overlapRow = collect($content['candidates'])->firstWhere('id', $overlap->id);
    $noOverlapRow = collect($content['candidates'])->firstWhere('id', $noOverlap->id);

    expect($overlapRow['score'])->toBeGreaterThan($noOverlapRow['score']);
});

test('has stock bonus is applied', function () {
    $category = Category::factory()->create();

    $source = AssetModel::factory()->create(['category_id' => $category->id]);
    $withStock = AssetModel::factory()->create(['category_id' => $category->id]);
    Asset::factory()->count(2)->create(['model_id' => $withStock->id, 'assigned_to' => null]);

    $content = handle(['model_id' => $source->id])->getStructuredContent();

    $row = collect($content['candidates'])->firstWhere('id', $withStock->id);

    expect($row['reasons'])->toContain('has_stock');
    expect($row['available_count'])->toBe(2);
});

test('require available inventory excludes out of stock candidates', function () {
    $category = Category::factory()->create();

    $source = AssetModel::factory()->create(['category_id' => $category->id]);
    $outOfStock = AssetModel::factory()->create(['category_id' => $category->id]);
    $withStock = AssetModel::factory()->create(['category_id' => $category->id]);
    Asset::factory()->create(['model_id' => $withStock->id, 'assigned_to' => null]);

    $content = handle([
        'model_id' => $source->id,
        'require_available_inventory' => true,
    ])->getStructuredContent();

    $returnedIds = array_column($content['candidates'], 'id');
    expect($returnedIds)->toContain($withStock->id);
    expect($returnedIds)->not->toContain($outOfStock->id);
});

test('source model is excluded from candidates', function () {
    $category = Category::factory()->create();
    $source = AssetModel::factory()->create(['category_id' => $category->id]);

    $content = handle(['model_id' => $source->id])->getStructuredContent();

    $returnedIds = array_column($content['candidates'], 'id');
    expect($returnedIds)->not->toContain($source->id);
});

test('limit parameter caps returned candidates', function () {
    $category = Category::factory()->create();
    $source = AssetModel::factory()->create(['category_id' => $category->id]);
    AssetModel::factory()->count(5)->create(['category_id' => $category->id]);

    $content = handle([
        'model_id' => $source->id,
        'limit' => 2,
    ])->getStructuredContent();

    expect(count($content['candidates']))->toBeLessThanOrEqual(2);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $source = AssetModel::factory()->create();

    expect(handle(['model_id' => $source->id])->responses()->first()->isError())->toBeTrue();
});
