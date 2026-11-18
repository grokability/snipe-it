<?php

use App\Mcp\Tools\CreateCategoryTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createCategories()->create());
});

function createCategoryTool(array $args = []): ResponseFactory
{
    return (new CreateCategoryTool)->handle(new Request($args));
}

test('creates asset category', function () {
    $content = createCategoryTool([
        'name' => 'Test Asset Category',
        'category_type' => 'asset',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('categories', ['name' => 'Test Asset Category', 'category_type' => 'asset']);
});

test('creates license category', function () {
    $content = createCategoryTool([
        'name' => 'Test License Category',
        'category_type' => 'license',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('categories', ['name' => 'Test License Category', 'category_type' => 'license']);
});

test('response includes id name and type', function () {
    $content = createCategoryTool([
        'name' => 'Response Category',
        'category_type' => 'accessory',
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Category');
    expect($content['category_type'])->toEqual('accessory');
});

test('returns error when name missing', function () {
    expect(createCategoryTool(['category_type' => 'asset'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when type missing', function () {
    expect(createCategoryTool(['name' => 'No Type Category'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when invalid type', function () {
    expect(createCategoryTool([
        'name' => 'Invalid Type Category',
        'category_type' => 'invalid',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createCategoryTool([
        'name' => 'Blocked Category',
        'category_type' => 'asset',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseMissing('categories', ['name' => 'Blocked Category']);
});
