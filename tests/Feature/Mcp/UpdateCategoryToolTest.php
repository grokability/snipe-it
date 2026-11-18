<?php

use App\Mcp\Tools\UpdateCategoryTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editCategories()->create());
});

function updateCategoryTool(array $args = []): ResponseFactory
{
    return (new UpdateCategoryTool)->handle(new Request($args));
}

test('updates category by id', function () {
    $category = Category::factory()->create(['name' => 'Original Cat Name']);

    $content = updateCategoryTool([
        'id' => $category->id,
        'new_name' => 'Updated Cat Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Cat Name']);
});

test('renames via new name', function () {
    $category = Category::factory()->create(['name' => 'Before Cat Rename']);

    $content = updateCategoryTool([
        'id' => $category->id,
        'new_name' => 'After Cat Rename',
    ])->getStructuredContent();

    expect($content['name'])->toEqual('After Cat Rename');
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'After Cat Rename']);
});

test('returns error when not found', function () {
    expect(updateCategoryTool(['id' => 999999, 'new_name' => 'Ghost Cat'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $category = Category::factory()->create(['name' => 'Unchanged Cat']);
    $this->actingAs(User::factory()->create());

    expect(updateCategoryTool([
        'id' => $category->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Unchanged Cat']);
});
