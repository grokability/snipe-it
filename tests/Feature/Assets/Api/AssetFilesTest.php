<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AssetFilesTest extends TestCase
{
    public function testAssetApiAcceptsFileUpload()
    {
        // Upload a file to an asset

        // Create an asset to work with
        $asset = Asset::factory()->count(1)->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($user)
            ->post(
               route('api.assets.files.store', $asset), [
		       'file' => [UploadedFile::fake()->create("test.jpg", 100)]
	       ])
	       ->assertOk();
    }

    public function testAssetApiListsFiles()
    {
        // List all files on an asset
        
        // Create an asset to work with
        $asset = Asset::factory()->count(1)->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(route('api.assets.files.index', $asset))
                ->assertOk()
		        ->assertJsonStructure([
                    'rows',
                    'total',
		    ]);
    }

    public function testAssetApiDownloadsFile()
    {
        // Download a file from an asset

        // Create an asset to work with
        $asset = Asset::factory()->count(1)->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($user)
            ->post(route('api.assets.files.store', $asset), [
		       'file' => [UploadedFile::fake()->create("test.jpg", 100)]
	         ])
            ->assertOk();

        // List the files to get the file ID
        $result = $this->actingAsForApi($user)
            ->getJson(route('api.assets.files.index', $asset))
            ->assertOk();
    }

    public function testAssetApiDeletesFile()
    {
        // Delete a file from an asset

        // Create an asset to work with
        $asset = Asset::factory()->count(1)->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        //Upload a file
        $this->actingAsForApi($user)
            ->post(
               route('api.assets.files.store', $asset), [
		       'file' => [UploadedFile::fake()->create("test.jpg", 100)]
	       ])
	       ->assertOk();

        // List the files to get the file ID
        $result = $this->actingAsForApi($user)
            ->getJson(
		    route('api.assets.files.index', $asset))
                ->assertOk();
        
    }
}
