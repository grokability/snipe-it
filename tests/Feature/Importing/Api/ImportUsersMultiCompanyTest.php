<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Company;
use App\Models\Import;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\UsersImportFileBuilder as ImportFileBuilder;

class ImportUsersMultiCompanyTest extends ImportDataTestCase
{
    use CleansUpImportFiles;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'user';
        }

        return parent::importFileResponse($parameters);
    }

    public function test_pipe_separated_company_names_create_multiple_pivot_entries()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $companyA->name.'|'.$companyB->name,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertOk();

        $user = User::where('username', $row['username'])->firstOrFail();

        $this->assertCount(2, $user->companies, 'User should belong to both pipe-separated companies');
        $this->assertTrue($user->companies->contains($companyA));
        $this->assertTrue($user->companies->contains($companyB));
    }

    public function test_pipe_separated_companies_create_new_companies_when_not_found()
    {
        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => 'Acme Corp|Widget Inc',
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertOk();

        $user = User::where('username', $row['username'])->firstOrFail();

        $this->assertCount(2, $user->companies, 'User should belong to two newly-created companies');

        $names = $user->companies->pluck('name')->all();
        $this->assertContains('Acme Corp', $names);
        $this->assertContains('Widget Inc', $names);
    }

    public function test_single_company_name_without_pipe_works_as_before()
    {
        $company = Company::factory()->create();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertOk();

        $user = User::where('username', $row['username'])->firstOrFail();

        $this->assertCount(1, $user->companies);
        $this->assertTrue($user->companies->contains($company));
    }

    public function test_blank_company_column_leaves_user_without_companies()
    {
        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => '',
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertOk();

        $user = User::where('username', $row['username'])->firstOrFail();

        $this->assertCount(0, $user->companies, 'Blank company column should leave user with no companies');
    }

    public function test_non_superuser_cannot_create_floater_user_via_import()
    {
        // #19200: non-superuser importing a new user with no resolvable
        // company while floater mode is on would have minted a floater
        // account — exploitable via the welcome-email + activated columns
        // to hand the attacker valid credentials. Importer must skip the row
        // and leave no user behind.
        $this->settings->enableFloaterMode();

        $importerCompany = Company::factory()->create();
        $importer = $importerCompany->users()->save(
            User::factory()->editUsers()->createUsers()->canImport()->create(),
        );

        $importFileBuilder = ImportFileBuilder::new(['companyName' => '']);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi($importer)
            ->importFileResponse(['import' => $import->id]);

        $this->assertDatabaseMissing('users', [
            'username' => $row['username'],
        ]);
    }
}
