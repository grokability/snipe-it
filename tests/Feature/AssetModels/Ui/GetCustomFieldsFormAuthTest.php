<?php

use App\Models\AssetModel;
use App\Models\User;

/**
 * Regression coverage for GHSA-cpxg-ch29-fq53.
 *
 * AssetModelsController::getCustomFields (route models/{id}/custom_fields,
 * name custom_fields/model) shipped without any authorize() call. Every
 * other action in that controller has one. Any authenticated account,
 * including a user whose permission JSON was literally `{}`, could hit
 * the endpoint and enumerate every model's custom-field schema (labels,
 * generated column names, help text, listbox/checkbox/radio option sets,
 * required flags, per-model defaults) by iterating IDs.
 *
 * Fix authorizes any-of (assets.create, assets.update, models.view) to
 * match the three legit caller shapes: the asset create form's model
 * change XHR, the asset edit form's model change XHR, and the model
 * edit page's custom-field preview.
 */
test('zero permission user cannot load custom fields form', function () {
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('custom_fields/model', $model->id))
        ->assertForbidden();
});

test('user with models view can load custom fields form', function () {
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->viewAssetModels()->create())
        ->get(route('custom_fields/model', $model->id))
        ->assertOk();
});

test('asset creator can load custom fields form', function () {
    // The endpoint is fired from the asset create form's model-select
    // XHR. An account with assets.create but no models.view still has
    // to be able to load the model's custom-field partial.
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->createAssets()->create())
        ->get(route('custom_fields/model', $model->id))
        ->assertOk();
});

test('asset editor can load custom fields form', function () {
    // Same shape as the create case for the /hardware/{id}/edit form.
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->editAssets()->create())
        ->get(route('custom_fields/model', $model->id))
        ->assertOk();
});

test('superuser can load custom fields form', function () {
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('custom_fields/model', $model->id))
        ->assertOk();
});
