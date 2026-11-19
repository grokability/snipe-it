<?php

use App\Models\Accessory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Swap the private and public disks for in-memory fakes so
    // uploaded files don't accumulate under storage/private_uploads
    // and public/uploads across test runs. Was missing here, so
    // every run of this file left real bytes on disk.
    Storage::fake('local');
    Storage::fake('public');
});

test('accessory api accepts file upload', function () {
    // Upload a file to a model

    // Create a model to work with
    $accessory = Accessory::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // Upload a file
    $this->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'accessories', 'id' => $accessory->id]), [
                'file' => [UploadedFile::fake()->create('test.jpg', 100)],
            ]
        )
        ->assertOk();
});

test('accessory api lists files', function () {
    // List all files on a model

    // Create a model to work with
    $accessory = Accessory::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // List the files
    $this->actingAsForApi($user)
        ->getJson(
            route('api.files.index', ['object_type' => 'accessories', 'id' => $accessory->id])
        )
        ->assertOk()
        ->assertJsonStructure(
            [
                'rows',
                'total',
            ]
        );
});

test('accessory fails if invalid type passed in url', function () {
    // List all files on a model

    // Create an model to work with
    $accessory = Accessory::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // List the files
    $this->actingAsForApi($user)
        ->getJson(
            route('api.files.index', ['object_type' => 'shibboleeeeeet', 'id' => $accessory->id])
        )
        ->assertStatus(404);
});

test('accessory fails if invalid id passed in url', function () {
    // List all files on a model

    // Create an model to work with
    $accessory = Accessory::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // List the files
    $this->actingAsForApi($user)
        ->getJson(
            route('api.files.index', ['object_type' => 'accessories', 'id' => 100000])
        )
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('accessory api downloads file', function () {
    // Download a file from a model

    // Create a model to work with
    $accessory = Accessory::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // Upload a file
    $this->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'accessories', 'id' => $accessory->id]), [
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
            route('api.files.store', ['object_type' => 'accessories', 'id' => $accessory->id]), [
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
            route('api.files.index', ['object_type' => 'accessories', 'id' => $accessory->id, 'order' => 'asc'])
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
                    'object_type' => 'accessories',
                    'id' => $accessory->id,
                    'file_id' => $result->decodeResponseJson()->json()['rows'][0]['id'],
                ]
            )
        )
        ->assertOk();
});

test('accessory api deletes file', function () {
    // Delete a file from a model

    // Create a model to work with
    $accessory = Accessory::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // Upload a file
    $this->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'accessories', 'id' => $accessory->id]), [
                'file' => [UploadedFile::fake()->create('test.jpg', 100)],
            ]
        )
        ->assertOk();

    // List the files to get the file ID
    $result = $this->actingAsForApi($user)
        ->getJson(
            route('api.files.index', ['object_type' => 'accessories', 'id' => $accessory->id])
        )
        ->assertOk();

    // Delete the file
    $this->actingAsForApi($user)
        ->delete(
            route(
                'api.files.destroy', [
                    'object_type' => 'accessories',
                    'id' => $accessory->id,
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
});
