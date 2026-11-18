<?php

use App\Mcp\Tools\ShowCategoryTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewCategories()->create());
});

function showCategoryTool(array $args = []): ResponseFactory
{
    return (new ShowCategoryTool)->handle(new Request($args));
}

test('returns category by id', function () {
    $category = Category::factory()->create(['name' => 'Lookup By ID Cat']);

    $content = showCategoryTool(['id' => $category->id])->getStructuredContent();

    expect($content['id'])->toEqual($category->id);
    expect($content['name'])->toEqual('Lookup By ID Cat');
});

test('returns category by name', function () {
    $category = Category::factory()->create(['name' => 'Lookup By Name Cat']);

    $content = showCategoryTool(['name' => 'Lookup By Name Cat'])->getStructuredContent();

    expect($content['id'])->toEqual($category->id);
    expect($content['name'])->toEqual('Lookup By Name Cat');
});

test('returns error when no identifier provided', function () {
    expect(showCategoryTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showCategoryTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(showCategoryTool(['id' => $category->id])->responses()->first()->isError())->toBeTrue();
});
