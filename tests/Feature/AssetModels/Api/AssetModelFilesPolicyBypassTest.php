<?php

use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

test('asset viewer can list model files', function () {
    // Seed one file on the model as a superuser so there is something to see.
    $model = AssetModel::factory()->create();
    uploadFileAs(User::factory()->superuser()->create(), $model);

    // Anyone who can view assets can see the model's file attachments
    // (user manuals, spec sheets) because they show up on the asset
    // detail page and apply to every asset of that model.
    $reader = User::factory()->viewAssets()->create();

    $this->actingAsForApi($reader)
        ->getJson(route('api.files.index', ['object_type' => 'models', 'id' => $model->id]))
        ->assertOk()
        ->assertJsonPath('total', 1);
});

test('asset viewer can download model file', function () {
    // Seed one file as superuser.
    $model = AssetModel::factory()->create();
    $fileId = uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

    $reader = User::factory()->viewAssets()->create();

    $this->actingAsForApi($reader)
        ->get(route('api.files.show', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
        ->assertOk();
});

test('asset viewer cannot upload to model', function () {
    $model = AssetModel::factory()->create();
    $writer = User::factory()->viewAssets()->create();

    $this->actingAsForApi($writer)
        ->post(
            route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
            ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
        )
        ->assertForbidden();
});

test('asset viewer cannot delete model file', function () {
    // Seed a file as superuser so there is something for the low-priv user to try to delete.
    $model = AssetModel::factory()->create();
    $fileId = uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

    $writer = User::factory()->viewAssets()->create();

    $this->actingAsForApi($writer)
        ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
        ->assertForbidden();
});

test('user with only assets files cannot upload to model', function () {
    // The originally-reported bypass scenario: assets.files alone used to
    // grant model file upload/delete. It must not anymore.
    $model = AssetModel::factory()->create();
    $writer = User::factory()->manageAssetFiles()->create();

    $this->actingAsForApi($writer)
        ->post(
            route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
            ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
        )
        ->assertForbidden();
});

test('user with only assets files cannot delete model file', function () {
    $model = AssetModel::factory()->create();
    $fileId = uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

    $writer = User::factory()->manageAssetFiles()->create();

    $this->actingAsForApi($writer)
        ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
        ->assertForbidden();
});

test('user with models files can upload to model', function () {
    $model = AssetModel::factory()->create();
    $writer = User::factory()->manageModelFiles()->create();

    $this->actingAsForApi($writer)
        ->post(
            route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
            ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
        )
        ->assertOk();
});

test('user with models files can delete model file', function () {
    $model = AssetModel::factory()->create();
    $fileId = uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

    $writer = User::factory()->manageModelFiles()->create();

    $this->actingAsForApi($writer)
        ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
        ->assertOk();
});

test('user with no file permissions is forbidden on all actions', function () {
    $model = AssetModel::factory()->create();
    $fileId = uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

    $noPerms = User::factory()->create();

    $this->actingAsForApi($noPerms)
        ->getJson(route('api.files.index', ['object_type' => 'models', 'id' => $model->id]))
        ->assertForbidden();

    $this->actingAsForApi($noPerms)
        ->get(route('api.files.show', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
        ->assertForbidden();

    $this->actingAsForApi($noPerms)
        ->post(
            route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
            ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
        )
        ->assertForbidden();

    $this->actingAsForApi($noPerms)
        ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
        ->assertForbidden();
});

function uploadFileAs(User $user, AssetModel $model): void
{
    test()->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
            ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
        )
        ->assertOk();
}

function uploadFileAndReturnId(User $user, AssetModel $model): int
{
    uploadFileAs($user, $model);

    return test()->actingAsForApi($user)
        ->getJson(route('api.files.index', ['object_type' => 'models', 'id' => $model->id]))
        ->assertOk()
        ->decodeResponseJson()
        ->json()['rows'][0]['id'];
}
