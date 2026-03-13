<?php

namespace Tests\Feature\Users\Api;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UserFileTest extends TestCase
{
    public function testUserApiAcceptsFileUpload()
    {
        // Create a model to work with
        $user = User::factory()->create();

        // Create a superuser to run this as
        $admin = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($admin)
            ->post(
                route('api.files.store', ['object_type' => 'users', 'id' => $user->id]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)]
                ]
            )
            ->assertOk();
    }

    public function testUserApiListsFiles()
    {
        // List all files on a model

        // Create a model to work with
        $user = User::factory()->create();

        // Create a superuser to run this as
        $admin = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($admin)
            ->getJson(
                route('api.files.index', ['object_type' => 'users', 'id' => $user->id])
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                'rows',
                'total',
                ]
            );
    }

    public function testUserFailsIfInvalidTypePassedInUrl()
    {
        // List all files on a model

        // Create an model to work with
        $user = User::factory()->create();

        // Create a superuser to run this as
        $admin = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($admin)
            ->getJson(
                route('api.files.index', ['object_type' => 'shibboleeeeeet', 'id' => $user->id])
            )
            ->assertStatus(404);
    }

    public function testUserFailsIfInvalidIdPassedInUrl()
    {
        // List all files on a model

        // Create an model to work with
        $user = User::factory()->create();

        // Create a superuser to run this as
        $admin = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($admin)
            ->getJson(
                route('api.files.index', ['object_type' => 'users', 'id' => 100000])
            )
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testUserApiDownloadsFile()
    {
        // Download a file from a model

        // Create a model to work with
        $user = User::factory()->create();

        // Create a superuser to run this as
        $admin = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($admin)
            ->post(
                route('api.files.store', ['object_type' => 'users', 'id' => $user->id]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)],
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
        $this->actingAsForApi($admin)
            ->post(
                route('api.files.store', ['object_type' => 'users', 'id' => $user->id]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)],
                'notes' => 'manual'
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
        $result = $this->actingAsForApi($admin)
            ->getJson(
                route('api.files.index', ['object_type' => 'users', 'id' => $user->id, 'order' => 'asc'])
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                'total',
                'rows'=>[
                    '*' => [
                        'id',
                        'filename',
                        'url',
                        'created_by',
                        'created_at',
                        'deleted_at',
                        'note',
                        'available_actions'
                    ]
                ]
                ]
            )
            ->assertJsonPath('rows.0.note', null)
            ->assertJsonPath('rows.1.note', 'manual');

        // Get the file
        $this->actingAsForApi($admin)
            ->get(
                route(
                    'api.files.show', [
                    'object_type' => 'users',
                    'id' => $user->id,
                    'file_id' => $result->decodeResponseJson()->json()["rows"][0]["id"],
                    ]
                )
            )
            ->assertOk();
    }

    public function testUserApiDeletesFile()
    {
        // Delete a file from a model

        // Create a model to work with
        $user = User::factory()->create();

        // Create a superuser to run this as
        $admin = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($admin)
            ->post(
                route('api.files.store', ['object_type' => 'users', 'id' => $user->id]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)]
                ]
            )
            ->assertOk();

        // List the files to get the file ID
        $result = $this->actingAsForApi($admin)
            ->getJson(
                route('api.files.index', ['object_type' => 'users', 'id' => $user->id])
            )
            ->assertOk();

        // Delete the file
        $this->actingAsForApi($admin)
            ->delete(
                route(
                    'api.files.destroy', [
                    'object_type' => 'users',
                    'id' => $user->id,
                    'file_id' => $result->decodeResponseJson()->json()["rows"][0]["id"],
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
}