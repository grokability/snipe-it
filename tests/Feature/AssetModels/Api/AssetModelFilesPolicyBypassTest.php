<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression tests for a broken-access-control bug where a user with only
 * `assets.files` (and not `models.files`) could upload and delete files on
 * any asset model instance-wide. The `AssetModelPolicy::files()` method
 * short-circuited on `assets.files`, and the controller used the same
 * `files` ability for both read and write actions, so the intended write
 * gate `models.files` was bypassable.
 *
 * Fix: added `manageFiles()` as the write ability. AssetModelPolicy overrides
 * it to require `models.files` strictly. The read cascade on `files()` is
 * preserved so anyone who can view an asset can still see its model's file
 * attachments inline on the asset detail page.
 */
class AssetModelFilesPolicyBypassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_asset_viewer_can_list_model_files()
    {
        // Seed one file on the model as a superuser so there is something to see.
        $model = AssetModel::factory()->create();
        $this->uploadFileAs(User::factory()->superuser()->create(), $model);

        // Anyone who can view assets can see the model's file attachments
        // (user manuals, spec sheets) because they show up on the asset
        // detail page and apply to every asset of that model.
        $reader = User::factory()->viewAssets()->create();

        $this->actingAsForApi($reader)
            ->getJson(route('api.files.index', ['object_type' => 'models', 'id' => $model->id]))
            ->assertOk()
            ->assertJsonPath('total', 1);
    }

    public function test_asset_viewer_can_download_model_file()
    {
        // Seed one file as superuser.
        $model = AssetModel::factory()->create();
        $fileId = $this->uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

        $reader = User::factory()->viewAssets()->create();

        $this->actingAsForApi($reader)
            ->get(route('api.files.show', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
            ->assertOk();
    }

    public function test_asset_viewer_cannot_upload_to_model()
    {
        $model = AssetModel::factory()->create();
        $writer = User::factory()->viewAssets()->create();

        $this->actingAsForApi($writer)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertForbidden();
    }

    public function test_asset_viewer_cannot_delete_model_file()
    {
        // Seed a file as superuser so there is something for the low-priv user to try to delete.
        $model = AssetModel::factory()->create();
        $fileId = $this->uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

        $writer = User::factory()->viewAssets()->create();

        $this->actingAsForApi($writer)
            ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
            ->assertForbidden();
    }

    public function test_user_with_only_assets_files_cannot_upload_to_model()
    {
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
    }

    public function test_user_with_only_assets_files_cannot_delete_model_file()
    {
        $model = AssetModel::factory()->create();
        $fileId = $this->uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

        $writer = User::factory()->manageAssetFiles()->create();

        $this->actingAsForApi($writer)
            ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
            ->assertForbidden();
    }

    public function test_user_with_models_files_can_upload_to_model()
    {
        $model = AssetModel::factory()->create();
        $writer = User::factory()->manageModelFiles()->create();

        $this->actingAsForApi($writer)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();
    }

    public function test_user_with_models_files_can_delete_model_file()
    {
        $model = AssetModel::factory()->create();
        $fileId = $this->uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

        $writer = User::factory()->manageModelFiles()->create();

        $this->actingAsForApi($writer)
            ->delete(route('api.files.destroy', ['object_type' => 'models', 'id' => $model->id, 'file_id' => $fileId]))
            ->assertOk();
    }

    public function test_user_with_no_file_permissions_is_forbidden_on_all_actions()
    {
        $model = AssetModel::factory()->create();
        $fileId = $this->uploadFileAndReturnId(User::factory()->superuser()->create(), $model);

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
    }

    private function uploadFileAs(User $user, AssetModel $model): void
    {
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();
    }

    private function uploadFileAndReturnId(User $user, AssetModel $model): int
    {
        $this->uploadFileAs($user, $model);

        return $this->actingAsForApi($user)
            ->getJson(route('api.files.index', ['object_type' => 'models', 'id' => $model->id]))
            ->assertOk()
            ->decodeResponseJson()
            ->json()['rows'][0]['id'];
    }
}
