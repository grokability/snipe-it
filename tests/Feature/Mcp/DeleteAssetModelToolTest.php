<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteAssetModels()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes model by id', function () {
    $model = AssetModel::factory()->create();

    $content = handle(['id' => $model->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('models', ['id' => $model->id]);
});

test('deletes model by name', function () {
    $model = AssetModel::factory()->create();

    $content = handle(['name' => $model->name])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('models', ['id' => $model->id]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 99999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when model has assets', function () {
    $model = AssetModel::factory()->create();
    Asset::factory()->create(['model_id' => $model->id]);

    $response = handle(['id' => $model->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('models', ['id' => $model->id]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $model = AssetModel::factory()->create();

    expect(handle(['id' => $model->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('models', ['id' => $model->id]);
});
