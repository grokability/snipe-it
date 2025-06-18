<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AssetModelFilesTest extends TestCase
{
    public function testAssetModelApiAcceptsFileUpload()
    {
        // Upload a file to a model

        // Create a model to work with
        $model = AssetModel::factory()->count(1)->create();

	// Create a superuser to run this as
	$user = User::factory()->superuser()->create();

	//Upload a file
	$this->actingAsForApi($user)
            ->post(
               route('api.models.files.store', ['model_id' => $model[0]["id"]]), [
		       'file' => [UploadedFile::fake()->create("test.jpg", 100)]
	       ])
	       ->assertOk();
    }

    public function testAssetModelApiListsFiles()
    {
        // List all files on a model
        
        // Create an model to work with
        $model = AssetModel::factory()->count(1)->create();

	// Create a superuser to run this as
	$user = User::factory()->superuser()->create();

	// List the files
	$this->actingAsForApi($user)
            ->getJson(
		    route('api.models.files.index', ['model_id' => $model[0]["id"]]))
                ->assertOk()
                ->assertJsonStructure([
                    'rows',
                    'total',
                ]);
    }

    public function testAssetModelApiDownloadsFile()
    {
        // Download a file from a model

        // Create a model to work with
        $model = AssetModel::factory()->count(1)->create();

	// Create a superuser to run this as
	$user = User::factory()->superuser()->create();

	// Upload a file
	$this->actingAsForApi($user)
            ->post(
               route('api.models.files.store', ['model_id' => $model[0]["id"]]), [
		       'file' => [UploadedFile::fake()->create("test.jpg", 100)],
	       ])
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'messages',
            ]);

    // Upload a file with notes
    $this->actingAsForApi($user)
            ->post(
                route('api.models.files.store', ['model_id' => $model[0]["id"]]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)],
                'notes' => 'manual'
	       ])
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'messages',
            ]);

	// List the files to get the file ID
	$result = $this->actingAsForApi($user)
            ->getJson(
		    route('api.models.files.index', ['model_id' => $model[0]["id"]]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows'=>[
                    '*' => [
                        'id',
                        'filename',
                        'url',
                        'created_by',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'note',
                        'available_actions'
                    ]
                ]
            ])
            ->assertJsonPath('rows.0.note','')
            ->assertJsonPath('rows.1.note','manual');



	// Get the file
	$this->actingAsForApi($user)
            ->get(
               route('api.models.files.show', [
                   'model_id' => $model[0]["id"],
                   'file_id' => $result->decodeResponseJson()->json()["rows"][0]["id"],
	       ]))
	       ->assertOk();
    }

    public function testAssetModelApiDeletesFile()
    {
        // Delete a file from a model

        // Create a model to work with
        $model = AssetModel::factory()->count(1)->create();

	// Create a superuser to run this as
	$user = User::factory()->superuser()->create();

	//Upload a file
	$this->actingAsForApi($user)
            ->post(
               route('api.models.files.store', ['model_id' => $model[0]["id"]]), [
		       'file' => [UploadedFile::fake()->create("test.jpg", 100)]
	       ])
	       ->assertOk();

	// List the files to get the file ID
	$result = $this->actingAsForApi($user)
            ->getJson(
		    route('api.models.files.index', ['model_id' => $model[0]["id"]]))
                ->assertOk();

	// Delete the file
	$this->actingAsForApi($user)
            ->delete(
               route('api.models.files.destroy', [
                   'model_id' => $model[0]["id"],
                   'file_id' => $result->decodeResponseJson()->json()["rows"][0]["id"],
	       ]))
	       ->assertOk()
            ->assertJsonStructure([
            'status',
            'messages',
        ]);
    }
}
