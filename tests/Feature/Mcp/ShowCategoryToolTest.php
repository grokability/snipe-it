<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewCategories()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns category by id', function () {
    $category = Category::factory()->create(['name' => 'Lookup By ID Cat']);

    $content = handle(['id' => $category->id])->getStructuredContent();

    expect($content['id'])->toEqual($category->id);
    expect($content['name'])->toEqual('Lookup By ID Cat');
});

test('returns category by name', function () {
    $category = Category::factory()->create(['name' => 'Lookup By Name Cat']);

    $content = handle(['name' => 'Lookup By Name Cat'])->getStructuredContent();

    expect($content['id'])->toEqual($category->id);
    expect($content['name'])->toEqual('Lookup By Name Cat');
});

test('returns error when no identifier provided', function () {
    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $category = Category::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $category->id])->responses()->first()->isError())->toBeTrue();
});
