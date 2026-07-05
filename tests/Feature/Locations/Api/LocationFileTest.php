<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LocationFileTest extends TestCase
{
    public function test_location_api_accepts_file_upload()
    {
        // Create a model to work with
        $location = Location::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'locations', 'id' => $location->id]), [
                    'file' => [UploadedFile::fake()->create('test.jpg', 100)],
                ]
            )
            ->assertOk();
    }

    public function test_location_api_lists_files()
    {
        // List all files on a model

        // Create a model to work with
        $location = Location::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'locations', 'id' => $location->id])
            )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'rows',
                    'total',
                ]
            );
    }

    public function test_location_fails_if_invalid_type_passed_in_url()
    {
        // List all files on a model

        // Create an model to work with
        $location = Location::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'shibboleeeeeet', 'id' => $location->id])
            )
            ->assertStatus(404);
    }

    public function test_location_fails_if_invalid_id_passed_in_url()
    {
        // List all files on a model

        // Create an model to work with
        $location = Location::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // List the files
        $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'locations', 'id' => 100000])
            )
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_location_api_downloads_file()
    {
        // Download a file from a model

        // Create a model to work with
        $location = Location::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'locations', 'id' => $location->id]), [
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
                route('api.files.store', ['object_type' => 'locations', 'id' => $location->id]), [
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
                route('api.files.index', ['object_type' => 'locations', 'id' => $location->id, 'order' => 'asc'])
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
                        'object_type' => 'locations',
                        'id' => $location->id,
                        'file_id' => $result->decodeResponseJson()->json()['rows'][0]['id'],
                    ]
                )
            )
            ->assertOk();
    }

    public function test_location_api_deletes_file()
    {
        // Delete a file from a model

        // Create a model to work with
        $location = Location::factory()->create();

        // Create a superuser to run this as
        $user = User::factory()->superuser()->create();

        // Upload a file
        $this->actingAsForApi($user)
            ->post(
                route('api.files.store', ['object_type' => 'locations', 'id' => $location->id]), [
                    'file' => [UploadedFile::fake()->create('test.jpg', 100)],
                ]
            )
            ->assertOk();

        // List the files to get the file ID
        $result = $this->actingAsForApi($user)
            ->getJson(
                route('api.files.index', ['object_type' => 'locations', 'id' => $location->id])
            )
            ->assertOk();

        // Delete the file
        $this->actingAsForApi($user)
            ->delete(
                route(
                    'api.files.destroy', [
                        'object_type' => 'locations',
                        'id' => $location->id,
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

    public function test_non_superuser_can_list_location_files_with_fmcs_enabled()
    {
        // A location in the user's own company: upload logs get company_id = location.company_id.
        // Verify that FMCS scoping does not hide those logs from the owning user.
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);

        $superUser = User::factory()->superuser()->create();
        $normalUser = User::factory()
            ->manageLocationFiles()
            ->create(['company_id' => $company->id]);

        $this->actingAsForApi($superUser)
            ->post(
                route('api.files.store', ['object_type' => 'locations', 'id' => $location->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();

        $this->actingAsForApi($normalUser)
            ->getJson(route('api.files.index', ['object_type' => 'locations', 'id' => $location->id]))
            ->assertOk()
            ->assertJsonPath('total', 1);
    }

    public function test_user_in_different_company_cannot_access_location_files_with_fmcs_enabled()
    {
        // The policy must block a user from listing files for a location that belongs to a different company.
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $companyA->id]);

        $superUser = User::factory()->superuser()->create();
        $userInCompanyB = User::factory()
            ->manageLocationFiles()
            ->create(['company_id' => $companyB->id]);

        $this->actingAsForApi($superUser)
            ->post(
                route('api.files.store', ['object_type' => 'locations', 'id' => $location->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.files.index', ['object_type' => 'locations', 'id' => $location->id]))
            ->assertForbidden();
    }
}
