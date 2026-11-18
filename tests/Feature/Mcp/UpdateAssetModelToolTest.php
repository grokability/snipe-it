<?php

use App\Mcp\Tools\UpdateAssetModelTool;
use App\Models\AssetModel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAssetModels()->create());
});

function updateAssetModelTool(array $args = []): ResponseFactory
{
    return (new UpdateAssetModelTool)->handle(new Request($args));
}

test('updates model by id', function () {
    $model = AssetModel::factory()->create();

    $content = updateAssetModelTool([
        'id' => $model->id,
        'model_number' => 'MN-001',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('models', [
        'id' => $model->id,
        'model_number' => 'MN-001',
    ]);
});

test('renames via new name', function () {
    $model = AssetModel::factory()->create();

    $content = updateAssetModelTool([
        'id' => $model->id,
        'new_name' => 'Renamed Model',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('Renamed Model');
    $this->assertDatabaseHas('models', [
        'id' => $model->id,
        'name' => 'Renamed Model',
    ]);
});

test('returns error when not found', function () {
    expect(updateAssetModelTool(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $model = AssetModel::factory()->create();

    expect(updateAssetModelTool(['id' => $model->id, 'model_number' => 'HACKED'])->responses()->first()->isError())->toBeTrue();
});
