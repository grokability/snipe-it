<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\AssetModel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AssetModelFilesTest extends TestCase
{
    public function test_asset_model_api_accepts_file_upload()
    {
        // Upload a file to a model

        // Create a model to work with
        $model = AssetModel::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]), [
                    'file' => [UploadedFile::fake()->create('test.jpg', 100)],
                ]
            )
            ->assertOk();
    }

    public function test_asset_model_api_lists_files()
    {
        // List all files on a model

        // Create an model to work with
        $model = AssetModel::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'models', 'id' => $model->id])
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'rows',
                    'total',
                ]
            );
    }

    public function test_asset_model_fails_if_invalid_type_passed_in_url()
    {
        // List all files on a model

        // Create an model to work with
        $model = AssetModel::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'shibboleeeeeet', 'id' => $model->id])
            )
            ->assertStatus(404);
    }

    public function test_asset_model_fails_if_invalid_id_passed_in_url()
    {
        // List all files on a model

        // Create an model to work with
        $model = AssetModel::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'models', 'id' => 100000])
            )
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_asset_model_api_downloads_file()
    {
        // Download a file from a model

        // Create a model to work with
        $model = AssetModel::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]), [
                    'file' => [UploadedFile::fake()->create('test.jpg', 100)],
                ]
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'status',
                    'messages',
                ]
            );

        // Upload a file with notes
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]), [
                    'file' => [UploadedFile::fake()->create('test.jpg', 100)],
                    'notes' => 'manual',
                ]
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'status',
                    'messages',
                ]
            );

        // List the files to get the file ID
        $result = $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'models', 'id' => $model->id, 'sort' => 'id', 'order' => 'asc'])
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'total',
                    'rows' => [
                        '*' => [
                            'id',
                            'filename',
                            'url',
                            'created_by',
                            'created_at',
                            'deleted_at',
                            'note',
                            'available_actions',
                        ],
                    ],
                ]
            )
            ->assertJsonPath('rows.0.note', null)
            ->assertJsonPath('rows.1.note', 'manual');

        // Get the file
        $this->actingAsForApi($user)
            ->get(
                route(
                    'api.files.show', [
                        'object_type' => 'models',
                        'id' => $model->id,
                        'file_id' => $result->decodeResponseJson()->json()['rows'][0]['id'],
                    ]
                )
            )
            ->assertOk();
    }

    public function test_asset_model_api_deletes_file()
    {
        // Delete a file from a model

        // Create a model to work with
        $model = AssetModel::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]), [
                    'file' => [UploadedFile::fake()->create('test.jpg', 100)],
                ]
            )
            ->assertOk();

        // List the files to get the file ID
        $result = $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'models', 'id' => $model->id])
            )
            ->assertOk();

        // Delete the file
        $this->actingAsForApi($user)
            ->delete(
                route(
                    'api.files.destroy', [
                        'object_type' => 'models',
                        'id' => $model->id,
                        'file_id' => $result->decodeResponseJson()->json()['rows'][0]['id'],
                    ]
                )
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'status',
                    'messages',
                ]
            );
    }

    public function test_non_superuser_can_list_model_files_with_fmcs_enabled()
    {
        // AssetModel has no company_id column, so its upload logs get company_id = null.
        // With FMCS active the CompanyableScope previously applied WHERE company_id IN (...)
        // which excluded NULLs, making the files invisible to non-superusers.
        $this->settings->enableMultipleFullCompanySupport();

        $model = AssetModel::factory()->create();
        $company = Company::factory()->create();

        $superUser = User::factory()->superuser()->create();
        $normalUser = User::factory()
            ->manageModelFiles()
            ->create(['company_id' => $company->id]);

        // Superuser uploads a file (company_id on the log will be null)
        $this->actingAsForApi($superUser)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();

        // Non-superuser in a specific company should still see it
        $this->actingAsForApi($normalUser)
            ->getJson(route('api.files.index', ['object_type' => 'models', 'id' => $model->id]))
            ->assertOk()
            ->assertJsonPath('total', 1);
    }
}
