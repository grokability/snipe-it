<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;

test('clean model supports both edit and delete', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.show', $model))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', true)
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('soft deleted model supports neither', function () {
    // api.models.show excludes soft-deleted rows, so we assert via the
    // index endpoint with status=deleted which does return them.
    $model = AssetModel::factory()->create(['name' => 'unique-'.uniqid()]);
    $model->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.index', ['status' => 'deleted', 'search' => $model->name]))
        ->assertOk()
        ->assertJsonPath('rows.0.id', $model->id)
        ->assertJsonPath('rows.0.available_actions.bulk_selectable.edit', false)
        ->assertJsonPath('rows.0.available_actions.bulk_selectable.delete', false);
});

test('model with assigned assets cannot be deleted', function () {
    $model = AssetModel::factory()->create();
    Asset::factory()->create(['model_id' => $model->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.show', $model))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', true)
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});

test('user without edit permission gets edit false', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->viewAssetModels()->deleteAssetModels()->create())
        ->getJson(route('api.models.show', $model))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', false)
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('user without delete permission gets delete false', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->viewAssetModels()->editAssetModels()->create())
        ->getJson(route('api.models.show', $model))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', true)
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});
