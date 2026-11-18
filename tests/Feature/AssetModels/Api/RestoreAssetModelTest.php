<?php

use App\Models\Actionlog;
use App\Models\AssetModel;
use App\Models\User;

test('permission required to restore asset model', function () {
    $model = AssetModel::factory()->create();
    $model->delete();

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.models.restore', $model->id))
        ->assertStatus(403);
});

test('error returned if asset model does not exist', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.restore', 999999))
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('error returned if asset model is not deleted', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.restore', $model->id))
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('soft deleted asset model can be restored', function () {
    $model = AssetModel::factory()->create();
    $model->delete();
    expect($model->fresh()->deleted_at)->not->toBeNull();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.restore', $model->id))
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($model->fresh()->deleted_at)->toBeNull();
});

test('restore writes action log entry', function () {
    $model = AssetModel::factory()->create();
    $model->delete();

    $before = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'restore')
        ->count();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.restore', $model->id))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $after = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'restore')
        ->count();

    expect($after)->toBe($before + 1, 'Expected one restore action_log entry to be written.');
});

test('restore does not write an update action log', function () {
    // AssetModelObserver::updating fires during restore because
    // Laravel's restore() calls save(). Pre-fix that observer
    // logged an "update" entry showing `deleted_at` flipping to
    // null. AssetModelObserver now filters deleted_at + updated_at
    // out of the diff so no bogus update entry gets written on
    // restore. See the matching test in the web-side
    // RestoreAssetModelTest.
    $model = AssetModel::factory()->create();
    $model->delete();

    $before = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'update')
        ->count();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.restore', $model->id))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $after = Actionlog::where('item_type', AssetModel::class)
        ->where('item_id', $model->id)
        ->where('action_type', 'update')
        ->count();

    expect($after)->toBe($before, 'Expected no update action_log entry on restore.');
});
