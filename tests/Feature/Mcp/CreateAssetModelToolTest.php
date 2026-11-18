<?php

use App\Mcp\Tools\CreateAssetModelTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createAssetModels()->create());
});

function createAssetModelTool(array $args = []): ResponseFactory
{
    return (new CreateAssetModelTool)->handle(new Request($args));
}

test('creates model with required fields', function () {
    $category = Category::factory()->create(['category_type' => 'asset']);

    $content = createAssetModelTool([
        'name' => 'Test Model',
        'category_id' => $category->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('models', [
        'name' => 'Test Model',
        'category_id' => $category->id,
    ]);
});

test('response includes id name and category', function () {
    $category = Category::factory()->create(['category_type' => 'asset']);

    $content = createAssetModelTool([
        'name' => 'Response Model',
        'category_id' => $category->id,
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Model');
    expect($content['category_id'])->toEqual($category->id);
});

test('returns error when name missing', function () {
    $category = Category::factory()->create(['category_type' => 'asset']);

    expect(createAssetModelTool(['category_id' => $category->id])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category missing', function () {
    expect(createAssetModelTool(['name' => 'No Category Model'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create(['category_type' => 'asset']);

    expect(createAssetModelTool([
        'name' => 'Blocked Model',
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseMissing('models', ['name' => 'Blocked Model']);
});
