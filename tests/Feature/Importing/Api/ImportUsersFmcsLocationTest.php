<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Company;
use App\Models\Import;
use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\UsersImportFileBuilder as ImportFileBuilder;

/**
 * Validates that FMCS location scoping is enforced during user imports when
 * full_multiple_companies_support + scope_locations_fmcs are both enabled.
 */
class ImportUsersFmcsLocationTest extends ImportDataTestCase
{
    use CleansUpImportFiles;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'user';
        }

        return parent::importFileResponse($parameters);
    }

    public function test_user_and_location_in_same_company_is_imported()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'location' => $location->name,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $this->assertTrue(User::where('username', $row['username'])->exists(), 'User should be created when location matches company');
    }

    public function test_user_and_location_in_different_companies_is_rejected()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $location = Location::factory()->create(['company_id' => $companyB->id]);

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $companyA->name,
            'location' => $location->name,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertInternalServerError();

        $this->assertFalse(User::where('username', $row['username'])->exists(), 'User should not be created when location is in a different company');
    }

    public function test_null_company_location_with_floater_on_is_imported()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => null]);

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'location' => $location->name,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $this->assertTrue(User::where('username', $row['username'])->exists(), 'User should be created when location has no company and floater is on');
    }

    public function test_null_company_location_with_floater_off_is_rejected()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => null]);

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'location' => $location->name,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertInternalServerError();

        $this->assertFalse(User::where('username', $row['username'])->exists(), 'User should not be created when location has no company and floater is off');
    }
}
