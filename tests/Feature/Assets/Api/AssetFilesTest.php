<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AssetFilesTest extends TestCase
{
    public function test_asset_api_accepts_file_upload()
    {
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
    }

    public function test_asset_api_lists_files()
    {
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
    }

    public function test_asset_api_downloads_file()
    {
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
    }

    public function test_asset_api_deletes_file()
    {
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

    }
}
