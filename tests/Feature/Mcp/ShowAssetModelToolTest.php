<?php

use App\Mcp\Tools\ShowAssetModelTool;
use App\Models\AssetModel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssetModels()->create());
});

function showAssetModelTool(array $args = []): ResponseFactory
{
    return (new ShowAssetModelTool)->handle(new Request($args));
}

test('returns model by id', function () {
    $model = AssetModel::factory()->create();

    $content = showAssetModelTool(['id' => $model->id])->getStructuredContent();

    expect($content['id'])->toEqual($model->id);
    expect($content['name'])->toEqual($model->name);
});

test('returns model by name', function () {
    $model = AssetModel::factory()->create();

    $content = showAssetModelTool(['name' => $model->name])->getStructuredContent();

    expect($content['id'])->toEqual($model->id);
    expect($content['name'])->toEqual($model->name);
});

test('returns error when no identifier provided', function () {
    expect(showAssetModelTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showAssetModelTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $model = AssetModel::factory()->create();

    expect(showAssetModelTool(['id' => $model->id])->responses()->first()->isError())->toBeTrue();
});
