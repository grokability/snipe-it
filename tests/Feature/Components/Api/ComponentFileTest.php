<?php

namespace Tests\Feature\Components\Api;

use App\Models\Component;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ComponentFileTest extends TestCase
{
    public function testComponentApiAcceptsFileUpload()
    {
        // Create a model to work with
        $component = Component::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'components', 'id' => $component->id]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)]
                ]
            )
            ->assertOk();
    }

    public function testComponentApiListsFiles()
    {
        // List all files on a model

        // Create a model to work with
        $component = Component::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'components', 'id' => $component->id])
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                'rows',
                'total',
                ]
            );
    }

    public function testComponentFailsIfInvalidTypePassedInUrl()
    {
        // List all files on a model

        // Create an model to work with
        $component = Component::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'shibboleeeeeet', 'id' => $component->id])
            )
            ->assertStatus(404);
    }

    public function testComponentFailsIfInvalidIdPassedInUrl()
    {
        // List all files on a model

        // Create an model to work with
        $component = Component::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'components', 'id' => 100000])
            )
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testComponentApiDownloadsFile()
    {
        // Download a file from a model

        // Create a model to work with
        $component = Component::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'components', 'id' => $component->id]), [
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
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'components', 'id' => $component->id]), [
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
        $result = $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'components', 'id' => $component->id, 'order' => 'asc'])
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
        $this->actingAsForApi($user)
            ->get(
                route(
                    'api.files.show', [
                    'object_type' => 'components',
                    'id' => $component->id,
                    'file_id' => $result->decodeResponseJson()->json()["rows"][0]["id"],
                    ]
                )
            )
            ->assertOk();
    }

    public function testComponentApiDeletesFile()
    {
        // Delete a file from a model

        // Create a model to work with
        $component = Component::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'components', 'id' => $component->id]), [
                'file' => [UploadedFile::fake()->create("test.jpg", 100)]
                ]
            )
            ->assertOk();

        // List the files to get the file ID
        $result = $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'components', 'id' => $component->id])
            )
            ->assertOk();

        // Delete the file
        $this->actingAsForApi($user)
            ->delete(
                route(
                    'api.files.destroy', [
                    'object_type' => 'components',
                    'id' => $component->id,
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
