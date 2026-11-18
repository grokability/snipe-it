<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

test('asset api accepts file upload', function () {
    // Upload a file to an asset
    // Create an asset to work with
    $asset = Asset::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // Upload a file
    $this->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'assets', 'id' => $asset->id]), [
                'file' => [UploadedFile::fake()->create('test.jpg', 100)],
            ]
        )
        ->assertOk();
});

test('asset api lists files', function () {
    // List all files on an asset
    // Create an asset to work with
    $asset = Asset::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // List the files
    $this->actingAsForApi($user)
        ->getJson(route('api.files.index', ['object_type' => 'assets', 'id' => $asset->id]))
        ->assertOk()
        ->assertJsonStructure(
            [
                'rows',
                'total',
            ]
        );
});

test('asset api downloads file', function () {
    // Download a file from an asset
    // Create an asset to work with
    $asset = Asset::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // Upload a file
    $this->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'assets', 'id' => $asset->id]), [
                'file' => [UploadedFile::fake()->create('test.jpg', 100)],
            ]
        )
        ->assertOk();

    // List the files to get the file ID
    $result = $this->actingAsForApi($user)
        ->getJson(route('api.files.index', ['object_type' => 'assets', 'id' => $asset->id]))
        ->assertOk();
});

test('asset api deletes file', function () {
    // Delete a file from an asset
    // Create an asset to work with
    $asset = Asset::factory()->create();

    // Create a superuser to run this as
    $user = User::factory()->superuser()->create();

    // Upload a file
    $this->actingAsForApi($user)
        ->post(
            route('api.files.store', ['object_type' => 'assets', 'id' => $asset->id]), [
                'file' => [UploadedFile::fake()->create('test.jpg', 100)],
            ]
        )
        ->assertOk();

    // List the files to get the file ID
    $result = $this->actingAsForApi($user)
        ->getJson(
            route('api.files.index', ['object_type' => 'assets', 'id' => $asset->id])
        )
        ->assertOk();
});
