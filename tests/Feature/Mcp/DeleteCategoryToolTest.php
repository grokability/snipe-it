<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteCategories()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes category by id', function () {
    $category = Category::factory()->create();

    $content = handle(['id' => $category->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('deletes category by name', function () {
    $category = Category::factory()->create(['name' => 'Delete By Name Cat']);

    $content = handle(['name' => 'Delete By Name Cat'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $category->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
});
