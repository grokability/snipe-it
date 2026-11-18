<?php

use App\Mcp\Tools\ListCategoriesTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewCategories()->create());
});

function listCategoriesTool(array $args = []): ResponseFactory
{
    return (new ListCategoriesTool)->handle(new Request($args));
}

test('returns categories with pagination metadata', function () {
    Category::factory()->count(3)->create();

    $content = listCategoriesTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('categories');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('offset');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each category includes key fields', function () {
    Category::factory()->create();

    $content = listCategoriesTool()->getStructuredContent();

    expect($content['categories'])->not->toBeEmpty();
    $category = $content['categories'][0];
    expect($category)->toHaveKey('id');
    expect($category)->toHaveKey('name');
    expect($category)->toHaveKey('category_type');
});

test('filters by category type', function () {
    $assetCategory = Category::factory()->create(['category_type' => 'asset', 'name' => 'Asset Type Cat '.uniqid()]);
    $licenseCategory = Category::factory()->create(['category_type' => 'license', 'name' => 'License Type Cat '.uniqid()]);

    $content = listCategoriesTool(['category_type' => 'asset'])->getStructuredContent();

    $ids = array_column($content['categories'], 'id');
    expect($ids)->toContain($assetCategory->id);
    expect($ids)->not->toContain($licenseCategory->id);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listCategoriesTool()->responses()->first()->isError())->toBeTrue();
});
