<?php

use App\Mcp\Tools\DeleteCategoryTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteCategories()->create());
});

function deleteCategoryTool(array $args = []): ResponseFactory
{
    return (new DeleteCategoryTool)->handle(new Request($args));
}

test('deletes category by id', function () {
    $category = Category::factory()->create();

    $content = deleteCategoryTool(['id' => $category->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('deletes category by name', function () {
    $category = Category::factory()->create(['name' => 'Delete By Name Cat']);

    $content = deleteCategoryTool(['name' => 'Delete By Name Cat'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('returns error when not found', function () {
    expect(deleteCategoryTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(deleteCategoryTool(['id' => $category->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
});
