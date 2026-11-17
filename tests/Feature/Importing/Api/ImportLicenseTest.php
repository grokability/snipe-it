<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Actionlog as ActivityLog;
use App\Models\Company;
use App\Models\Import;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\LicensesImportFileBuilder as ImportFileBuilder;

class ImportLicenseTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'license';
        }

        return parent::importFileResponse($parameters);
    }

    #[Test]
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create());

        $this->importFileResponse(['import' => 44])->assertForbidden();
    }

    #[Test]
    public function user_with_import_assets_permission_can_import_licenses(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->license()->withStubFile()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function import_licenses(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload' => ['tally' => ['created' => 1, 'updated' => 0, 'skipped' => 0, 'errored' => 0]],
                'status' => 'success',
                'messages' => ['redirect_url' => route('licenses.index')],
            ]);

        $newLicense = License::query()
            ->withCasts(['reassignable' => 'bool'])
            ->with(['category', 'company', 'manufacturer', 'supplier'])
            ->where('serial', $row['serialNumber'])
            ->sole();

        $activityLogs = ActivityLog::query()
            ->where('item_type', License::class)
            ->where('item_id', $newLicense->id)
            ->get();

        $this->assertCount(2, $activityLogs);

        $this->assertEquals($row['licenseName'], $newLicense->name);
        $this->assertEquals($row['serialNumber'], $newLicense->serial);
        $this->assertEquals($row['purchaseDate'], $newLicense->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $newLicense->purchase_cost);
        $this->assertEquals($row['orderNumber'], $newLicense->order_number);
        $this->assertEquals($row['seats'], $newLicense->seats);
        $this->assertEquals($row['notes'], $newLicense->notes);
        $this->assertEquals($row['licensedToName'], $newLicense->license_name);
        $this->assertEquals($row['licensedToEmail'], $newLicense->license_email);
        $this->assertEquals($row['supplierName'], $newLicense->supplier->name);
        $this->assertEquals($row['companyName'], $newLicense->company->name);
        $this->assertEquals($row['category'], $newLicense->category->name);
        $this->assertEquals($row['expirationDate'], $newLicense->expiration_date->toDateString());
        $this->assertEquals($row['isMaintained'] === 'TRUE', $newLicense->maintained);
        $this->assertEquals($row['isReassignAble'] === 'TRUE', $newLicense->reassignable);
        $this->assertEquals('', $newLicense->purchase_order);
        $this->assertNull($newLicense->depreciation_id);
        $this->assertNull($newLicense->termination_date);
        $this->assertNull($newLicense->deprecate);
        $this->assertNull($newLicense->min_amt);
    }

    #[Test]
    public function will_ignore_unknown_columns_when_file_contains_unknown_columns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function will_not_create_new_license_when_name_and_serial_number_already_exist(): void
    {
        $license = License::factory()->create();

        $importFileBuilder = ImportFileBuilder::times(4)->replace([
            'itemName' => $license->name,
            'serialNumber' => $license->serial,
        ]);

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $probablyNewLicenses = License::query()
            ->where('name', $license->name)
            ->where('serial', $license->serial)
            ->get();

        $this->assertCount(1, $probablyNewLicenses);
    }

    #[Test]
    public function format_attributes(): void
    {
        $importFileBuilder = ImportFileBuilder::new([
            'expirationDate' => '2022/10/10',
        ]);

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newLicense = License::query()
            ->where('serial', $importFileBuilder->firstRow()['serialNumber'])
            ->sole();

        $this->assertEquals('2022-10-10', $newLicense->expiration_date->toDateString());
    }

    #[Test]
    public function will_not_create_new_company_when_company_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['companyName' => Str::random()]);
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newLicenses = License::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['company_id']);

        $this->assertCount(1, $newLicenses->pluck('company_id')->unique()->all());
    }

    #[Test]
    public function will_not_create_new_manufacturer_when_manufacturer_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['manufacturerName' => Str::random()]);
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newLicenses = License::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['manufacturer_id']);

        $this->assertCount(1, $newLicenses->pluck('manufacturer_id')->unique()->all());
    }

    #[Test]
    public function will_not_create_new_category_when_category_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['category' => $this->faker->company]);
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newLicenses = License::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['category_id']);

        $this->assertCount(1, $newLicenses->pluck('category_id')->unique()->all());
    }

    #[Test]
    public function when_required_columns_are_missing_in_import_file(): void
    {
        $importFileBuilder = ImportFileBuilder::times()
            ->replace(['name' => ''])
            ->forget(['seats']);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => ['tally' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errored' => 1]],
                'messages' => [
                    $row['licenseName'] => [
                        "License \"{$row['licenseName']}\"" => [
                            'seats' => ['The seats field is required.'],
                        ],
                    ],
                ],
            ]);

        $newLicenses = License::query()
            ->where('serial', $row['serialNumber'])
            ->get();

        $this->assertCount(0, $newLicenses);
    }

    #[Test]
    public function update_license_from_import(): void
    {
        $license = License::factory()->create();
        $importFileBuilder = ImportFileBuilder::new([
            'licenseName' => $license->name,
            'serialNumber' => $license->serial,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedLicense = License::query()
            ->with(['manufacturer', 'category', 'supplier'])
            ->where('serial', $row['serialNumber'])
            ->sole();

        $this->assertEquals($row['licenseName'], $updatedLicense->name);
        $this->assertEquals($row['serialNumber'], $updatedLicense->serial);
        $this->assertEquals($row['purchaseDate'], $updatedLicense->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $updatedLicense->purchase_cost);
        $this->assertEquals($row['orderNumber'], $updatedLicense->order_number);
        $this->assertEquals($row['seats'], $updatedLicense->seats);
        $this->assertEquals($row['notes'], $updatedLicense->notes);
        $this->assertEquals($row['licensedToName'], $updatedLicense->license_name);
        $this->assertEquals($row['licensedToEmail'], $updatedLicense->license_email);
        $this->assertEquals($row['supplierName'], $updatedLicense->supplier->name);
        $this->assertEquals($row['companyName'], $updatedLicense->company->name);
        $this->assertEquals($row['category'], $updatedLicense->category->name);
        $this->assertEquals($row['expirationDate'], $updatedLicense->expiration_date->toDateString());
        $this->assertEquals($row['isMaintained'] === 'TRUE', $updatedLicense->maintained);
        $this->assertEquals($row['isReassignAble'] === 'TRUE', $updatedLicense->reassignable);
        $this->assertEquals($license->purchase_order, $updatedLicense->purchase_order);
        $this->assertEquals($license->depreciation_id, $updatedLicense->depreciation_id);
        $this->assertEquals($license->termination_date, $updatedLicense->termination_date);
        $this->assertEquals($license->deprecate, $updatedLicense->deprecate);
        $this->assertEquals($license->min_amt, $updatedLicense->min_amt);
    }

    #[Test]
    public function import_persists_the_requestable_flag_from_csv(): void
    {
        // License gained the Requestable trait + a per-row column.
        // Pins that a CSV Requestable=TRUE on create lands as `1` in
        // the DB, matching the shape the accessory / consumable /
        // component importers ship.
        $this->actingAsForApi(User::factory()->superuser()->create());

        $importFileBuilder = ImportFileBuilder::new(['requestable' => 'TRUE']);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->license()->create([
            'file_path' => $importFileBuilder->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $import->id])->assertOk();

        $license = License::query()
            ->where('serial', $row['serialNumber'])
            ->sole();
        $this->assertTrue((bool) $license->requestable);
    }

    #[Test]
    public function update_mode_clears_field_when_csv_column_is_present_but_empty(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();
        $initialImport = Import::factory()->license()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);
        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $license = License::query()
            ->where('name', $initialRow['licenseName'])
            ->where('serial', $initialRow['serialNumber'])
            ->sole();

        // Sanity: initial import should have populated the fields we are
        // about to clear. Guards against a false negative if the fixture
        // shape changes and the fields default to null.
        $this->assertNotNull($license->expiration_date);
        $this->assertNotEmpty($license->license_email);
        $this->assertNotEmpty($license->notes);

        // Re-import with these columns present but empty. Empty CSV cells
        // should clear the corresponding DB fields, not preserve them.
        $clearedRow = array_merge($initialRow, [
            'expirationDate' => '',
            'licensedToEmail' => '',
            'notes' => '',
        ]);
        $clearFile = new ImportFileBuilder([$clearedRow]);
        $clearImport = Import::factory()->license()->create([
            'file_path' => $clearFile->saveToImportsDirectory(),
        ]);
        $this->importFileResponse([
            'import' => $clearImport->id,
            'import-update' => true,
        ])->assertOk();

        $license->refresh();
        $this->assertNull($license->expiration_date);
        $this->assertNull($license->license_email);
        $this->assertNull($license->notes);
    }

    #[Test]
    public function update_mode_preserves_fields_when_csv_column_is_absent(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();
        $initialImport = Import::factory()->license()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);
        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $license = License::query()
            ->where('name', $initialRow['licenseName'])
            ->where('serial', $initialRow['serialNumber'])
            ->sole();

        $originalEmail = $license->license_email;
        $originalNotes = $license->notes;
        $originalExpirationDate = $license->expiration_date?->toDateString();

        // Re-import with a CSV that only has the identity fields plus one
        // updated field. All other columns are absent from the CSV, so
        // their DB values must be preserved.
        // A short static order number keeps the assertion within License's
        // order_number varchar(50) limit regardless of what the initial row
        // faker produced. Prefixing the faker value overflowed on some seeds.
        $newOrderNumber = 'UPDATED-ORDER-123';

        $partialRow = [
            'licenseName' => $initialRow['licenseName'],
            'serialNumber' => $initialRow['serialNumber'],
            'orderNumber' => $newOrderNumber,
        ];
        $partialFile = new ImportFileBuilder([$partialRow]);
        $partialImport = Import::factory()->license()->create([
            'file_path' => $partialFile->saveToImportsDirectory(),
        ]);
        $this->importFileResponse([
            'import' => $partialImport->id,
            'import-update' => true,
        ])->assertOk();

        $license->refresh();
        $this->assertEquals($newOrderNumber, $license->order_number);
        $this->assertEquals($originalEmail, $license->license_email);
        $this->assertEquals($originalNotes, $license->notes);
        $this->assertEquals($originalExpirationDate, $license->expiration_date?->toDateString());
    }

    #[Test]
    public function update_mode_logs_license_update_in_actionlog(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();

        $initialImport = Import::factory()->license()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $license = License::query()
            ->where('name', $initialRow['licenseName'])
            ->where('serial', $initialRow['serialNumber'])
            ->sole();

        $updatedRow = array_merge($initialRow, [
            'orderNumber' => (string) $initialRow['orderNumber'].'-UPD',
        ]);

        $updateFile = new ImportFileBuilder([$updatedRow]);
        $updateImport = Import::factory()->license()->create([
            'file_path' => $updateFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $updateImport->id,
            'import-update' => true,
        ])->assertOk();

        $license->refresh();
        $this->assertEquals($updatedRow['orderNumber'], $license->order_number);

        $updateLog = ActivityLog::query()
            ->where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog, 'Expected an update action log entry after license importer update mode.');
    }

    #[Test]
    public function custom_column_mapping(): void
    {
        $faker = ImportFileBuilder::times()->definition();
        $row = [
            'category' => $faker['supplierName'],
            'companyName' => $faker['serialNumber'],
            'expirationDate' => $faker['seats'],
            'isMaintained' => $faker['purchaseDate'],
            'isReassignAble' => $faker['purchaseCost'],
            'licensedToName' => $faker['orderNumber'],
            'licensedToEmail' => $faker['notes'],
            'licenseName' => $faker['licenseName'],
            'manufacturerName' => $faker['category'],
            'notes' => $faker['companyName'],
            'orderNumber' => $faker['expirationDate'],
            'purchaseCost' => $faker['isMaintained'],
            'purchaseDate' => $faker['isReassignAble'],
            'seats' => $faker['licensedToName'],
            'serialNumber' => $faker['licensedToEmail'],
            'supplierName' => $faker['manufacturerName'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Category' => 'supplier',
                'Company' => 'serial',
                'expiration date' => 'seats',
                'maintained' => 'purchase_date',
                'reassignable' => 'purchase_cost',
                'Licensed To Name' => 'order_number',
                'Licensed To Email' => 'notes',
                'licenseName' => 'name',
                'manufacturer' => 'category',
                'Notes' => 'company',
                'Serial number' => 'license_email',
                'Order Number' => 'expiration_date',
                'purchase Cost' => 'maintained',
                'purchase Date' => 'reassignable',
                'seats' => 'license_name',
                'supplier' => 'manufacturer',
            ],
        ])->assertOk();

        $newLicense = License::query()
            ->with(['category', 'company', 'manufacturer', 'supplier'])
            ->where('serial', $row['companyName'])
            ->sole();

        $this->assertEquals($row['licenseName'], $newLicense->name);
        $this->assertEquals($row['companyName'], $newLicense->serial);
        $this->assertEquals($row['isMaintained'], $newLicense->purchase_date->toDateString());
        $this->assertEquals($row['isReassignAble'], $newLicense->purchase_cost);
        $this->assertEquals($row['licensedToName'], $newLicense->order_number);
        $this->assertEquals($row['expirationDate'], $newLicense->seats);
        $this->assertEquals($row['licensedToEmail'], $newLicense->notes);
        $this->assertEquals($row['seats'], $newLicense->license_name);
        $this->assertEquals($row['serialNumber'], $newLicense->license_email);
        $this->assertEquals($row['category'], $newLicense->supplier->name);
        $this->assertEquals($row['notes'], $newLicense->company->name);
        $this->assertEquals($row['manufacturerName'], $newLicense->category->name);
        $this->assertEquals($row['orderNumber'], $newLicense->expiration_date->toDateString());
        $this->assertEquals($row['purchaseCost'] === 'TRUE', $newLicense->maintained);
        $this->assertEquals($row['purchaseDate'] === 'TRUE', $newLicense->reassignable);
        $this->assertEquals('', $newLicense->purchase_order);
        $this->assertNull($newLicense->depreciation_id);
        $this->assertNull($newLicense->termination_date);
        $this->assertNull($newLicense->deprecate);
        $this->assertNull($newLicense->min_amt);
    }

    #[Test]
    public function import_license_checkout_is_blocked_when_fmcs_companies_differ(): void
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $user = User::factory()->forCompany($companyB)->create();
        $this->settings->enableMultipleFullCompanySupport();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $companyA->name,
            'checkoutUsername' => $user->username,
            'seats' => 5,
        ]);

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $license = License::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $checkedOutSeat = LicenseSeat::where('license_id', $license->id)->whereNotNull('assigned_to')->first();
        $this->assertNull($checkedOutSeat, 'License seat should not be checked out when item and user companies differ under FMCS');
    }

    #[Test]
    public function import_license_checkout_is_allowed_when_fmcs_companies_match(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->forCompany($company)->create();
        $this->settings->enableMultipleFullCompanySupport();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'checkoutUsername' => $user->username,
            'seats' => 5,
        ]);

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $license = License::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $checkedOutSeat = LicenseSeat::where('license_id', $license->id)->where('assigned_to', $user->id)->first();
        $this->assertNotNull($checkedOutSeat, 'License seat should be checked out when companies match under FMCS');
    }

    #[Test]
    public function import_license_checkout_is_blocked_when_floater_disabled_and_user_has_no_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->withoutCompany()->create();
        $this->settings->enableMultipleFullCompanySupport()->disableFloaterMode();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'checkoutUsername' => $user->username,
            'seats' => 5,
        ]);

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $license = License::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $checkedOutSeat = LicenseSeat::where('license_id', $license->id)->whereNotNull('assigned_to')->first();
        $this->assertNull($checkedOutSeat, 'License seat should not be checked out to a no-company user when floater mode is off');
    }

    #[Test]
    public function import_license_checkout_is_allowed_when_floater_enabled_and_user_has_no_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->withoutCompany()->create();
        $this->settings->enableFloaterMode();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'checkoutUsername' => $user->username,
            'seats' => 5,
        ]);

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $license = License::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $checkedOutSeat = LicenseSeat::where('license_id', $license->id)->where('assigned_to', $user->id)->first();
        $this->assertNotNull($checkedOutSeat, 'License seat should be checked out to a no-company user when floater mode is on');
    }

    #[Test]
    public function multiple_rows_without_serial_column_assign_seats_to_a_single_license_in_update_mode(): void
    {
        // Regression from the 8.7.0 importer rewrite. Users importing a
        // seat-assignment CSV (username + license name, no serial column)
        // were seeing every row create a new License in update mode instead
        // of matching to an existing null-serial license. Reason:
        // setItemFromCsvIfPresent skips absent columns so the insert path
        // stored serial as NULL, but the match query looked for serial = ''
        // which never matches NULL in SQL. Match now checks
        // (serial IS NULL OR serial = '').
        [$firstUser, $secondUser] = User::factory()->count(2)->create();
        $existingLicense = License::factory()->create([
            'serial' => null,
            'seats' => 5,
        ]);

        $rowShape = [
            'licenseName' => $existingLicense->name,
            'category' => 'Software',
            'seats' => 5,
        ];

        $file = new ImportFileBuilder([
            array_merge($rowShape, ['checkoutUsername' => $firstUser->username]),
            array_merge($rowShape, ['checkoutUsername' => $secondUser->username]),
        ]);

        $import = Import::factory()->license()->create(['file_path' => $file->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse([
            'import' => $import->id,
            'import-update' => true,
        ])->assertOk();

        // Only the one existing License should exist; both rows should have
        // matched into it. Without the fix, each row created a new license.
        $licenses = License::where('name', $existingLicense->name)->get();
        $this->assertCount(1, $licenses, 'Both rows should have matched the existing null-serial license, not created duplicates.');

        // Two seats should be checked out, one per row.
        $this->assertSame(2, LicenseSeat::where('license_id', $existingLicense->id)->whereNotNull('assigned_to')->count());
    }

    #[Test]
    public function import_records_error_when_license_runs_out_of_free_seats(): void
    {
        // Regression for #19467 follow-up. If a seat-assignment CSV exceeds
        // the target license's free-seat count, the trailing rows should
        // surface as errored tally entries with a specific "no free seats"
        // message, not silently be dropped on the floor.
        [$firstUser, $secondUser, $thirdUser] = User::factory()->count(3)->create();
        $existingLicense = License::factory()->create([
            'serial' => null,
            'seats' => 2,
        ]);

        $rowShape = [
            'licenseName' => $existingLicense->name,
            'category' => 'Software',
            'seats' => 2,
        ];

        $file = new ImportFileBuilder([
            array_merge($rowShape, ['checkoutUsername' => $firstUser->username]),
            array_merge($rowShape, ['checkoutUsername' => $secondUser->username]),
            array_merge($rowShape, ['checkoutUsername' => $thirdUser->username]),
        ]);

        $import = Import::factory()->license()->create(['file_path' => $file->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $response = $this->importFileResponse([
            'import' => $import->id,
            'import-update' => true,
        ]);

        // The API convention for import runs with any errored rows is HTTP
        // 500 (see Api\ImportController::importFile line 352). The interesting
        // assertion is that the tally reflects the failure and the message
        // surfaces the specific "no free seats" reason.
        $response->assertStatus(500);
        $response->assertJson(['status' => 'import-errors']);
        $this->assertSame(1, $response->json('payload.tally.errored'), 'The row that had no free seat should be counted as errored in the tally.');
        $this->assertStringContainsString('no free seats', $response->content());

        // Two seats successfully assigned (rows 1 and 2), third row's seat
        // assignment failed and was recorded.
        $this->assertSame(2, LicenseSeat::where('license_id', $existingLicense->id)->whereNotNull('assigned_to')->count());
    }

    #[Test]
    public function update_matches_existing_license_with_null_serial_when_csv_omits_serial(): void
    {
        // Same class of bug as multiple_rows_without_serial_column, on the
        // update path: an existing License with serial NULL in the DB and a
        // CSV row that omits the serial column altogether. The old match
        // query used serial = '' which does not match NULL, so a duplicate
        // was created. Now checks (serial IS NULL OR serial = '').
        $license = License::factory()->create(['serial' => null]);

        $file = new ImportFileBuilder([[
            'licenseName' => $license->name,
            'category' => 'Software',
            'seats' => 3,
            'notes' => 'Updated via seat-assign CSV',
        ]]);

        $import = Import::factory()->license()->create(['file_path' => $file->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse([
            'import' => $import->id,
            'import-update' => true,
        ])->assertOk();

        $this->assertSame(1, License::where('name', $license->name)->count(), 'Existing null-serial license should have been matched, not duplicated.');
        $this->assertSame('Updated via seat-assign CSV', $license->fresh()->notes);
    }
}
