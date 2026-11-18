<?php

use App\Models\Actionlog;
use App\Models\AssetModel;
use App\Models\User;

test('permission required to restore asset model', function () {
    $model = AssetModel::factory()->create();
    $model->delete();

    $this->actingAs(User::factory()->create())
        ->post(route('models.restore.store', $model->id))
        ->assertForbidden();
});

test('create permission alone cannot restore asset model', function () {
    // Regression: getRestore() used to authorize `create`, so a caller
    // holding models.create but not models.delete could reverse an
    // admin's soft-delete. Every sibling restore path in the project
    // (users, locations, manufacturers, assets, bulk assets, and this
    // model's API sibling Api\AssetModelsController::restore) authorizes
    // `delete` instead. Pinning the correct behavior here.
    $model = AssetModel::factory()->create();
    $model->delete();

    $this->actingAs(User::factory()->createAssetModels()->create())
        ->post(route('models.restore.store', $model->id))
        ->assertForbidden();

    expect($model->fresh()->deleted_at)->not->toBeNull('Restore should not have run.');
});

test('delete permission can restore asset model', function () {
    // The paired positive case: models.delete IS the right permission
    // for restore, matching every sibling restore action.
    $model = AssetModel::factory()->create();
    $model->delete();

    $this->actingAs(User::factory()->deleteAssetModels()->create())
        ->post(route('models.restore.store', $model->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($model->fresh()->deleted_at)->toBeNull();
});

test('soft deleted asset model can be restored via web', function () {
    $model = AssetModel::factory()->create();
    $model->delete();
    expect($model->fresh()->deleted_at)->not->toBeNull();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.restore.store', $model->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($model->fresh()->deleted_at)->toBeNull();
});

test('restore writes exactly one restore action log', function () {
    $model = AssetModel::factory()->create();
    $model->delete();

    $before = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'restore')
        ->count();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.restore.store', $model->id))
        ->assertRedirect();

    $after = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'restore')
        ->count();

    expect($after)->toBe($before + 1, 'Expected exactly one restore action_log entry (regression: was 2 pre-fix, one from the controller and one from the observer).');
});

test('restore does not write an update action log', function () {
    $model = AssetModel::factory()->create();
    $model->delete();

    $before = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'update')
        ->count();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.restore.store', $model->id))
        ->assertRedirect();

    $after = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'update')
        ->count();

    expect($after)->toBe($before, 'Expected no update action_log entry on restore (regression: was 1 pre-fix, from the updating observer catching deleted_at flipping to null via save()).');
});
