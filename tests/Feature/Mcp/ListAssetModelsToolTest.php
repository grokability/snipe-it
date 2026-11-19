<?php

use App\Mcp\Tools\ListAssetModelsTool;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssetModels()->create());
});

function listAssetModelsTool(array $args = []): ResponseFactory
{
    return (new ListAssetModelsTool)->handle(new Request($args));
}

test('returns models with pagination', function () {
    AssetModel::factory()->count(3)->create();

    $content = listAssetModelsTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('models');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each model has key fields', function () {
    AssetModel::factory()->create();

    $content = listAssetModelsTool()->getStructuredContent();

    expect($content['models'])->not->toBeEmpty();
    $first = $content['models'][0];
    expect($first)->toHaveKey('id');
    expect($first)->toHaveKey('name');
    expect($first)->toHaveKey('category');
});

test('filters by category id', function () {
    $category = Category::factory()->create(['category_type' => 'asset']);
    $model = AssetModel::factory()->create(['category_id' => $category->id]);
    AssetModel::factory()->create(); // another model with different category

    $content = listAssetModelsTool(['category_id' => $category->id])->getStructuredContent();

    $ids = array_column($content['models'], 'id');
    expect($ids)->toContain($model->id);
    foreach ($ids as $id) {
        expect(collect($content['models'])->firstWhere('id', $id)['category_id'])->toEqual($category->id);
    }
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listAssetModelsTool()->responses()->first()->isError())->toBeTrue();
});
