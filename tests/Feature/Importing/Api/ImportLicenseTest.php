<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Actionlog as ActivityLog;
use App\Models\Import;
use App\Models\License;
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
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'license';
        }

        return parent::importFileResponse($parameters);
    }

    #[Test]
    public function testRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create());

        $this->importFileResponse(['import' => 44])->assertForbidden();
    }

    #[Test]
    public function userWithImportAssetsPermissionCanImportLicenses(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->license()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function importLicenses(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('licenses.index')]
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
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function willNotCreateNewLicenseWhenNameAndSerialNumberAlreadyExist(): void
    {
        $license = License::factory()->create();

        $importFileBuilder = ImportFileBuilder::times(4)->replace([
            'itemName'     => $license->name,
            'serialNumber' => $license->serial
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
    public function formatAttributes(): void
    {
        $importFileBuilder = ImportFileBuilder::new([
            'expirationDate' => '2022/10/10'
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
    public function willNotCreateNewCompanyWhenCompanyExists(): void
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
    public function willNotCreateNewManufacturerWhenManufacturerExists(): void
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
    public function willNotCreateNewCategoryWhenCategoryExists(): void
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
    public function whenRequiredColumnsAreMissingInImportFile(): void
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
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    $row['licenseName'] => [
                        "License \"{$row['licenseName']}\"" => [
                            'seats' => ['The seats field is required.'],
                        ]
                    ]
                ]
            ]);

        $newLicenses = License::query()
            ->where('serial', $row['serialNumber'])
            ->get();

        $this->assertCount(0, $newLicenses);
    }

    #[Test]
    public function updateLicenseFromImport(): void
    {
        $license = License::factory()->create();
        $importFileBuilder = ImportFileBuilder::new([
            'licenseName'  => $license->name,
            'serialNumber' => $license->serial
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
    public function customColumnMapping(): void
    {
        $faker = ImportFileBuilder::times()->definition();
        $row = [
            'category'         => $faker['supplierName'],
            'companyName'      => $faker['serialNumber'],
            'expirationDate'   => $faker['seats'],
            'isMaintained'     => $faker['purchaseDate'],
            'isReassignAble'   => $faker['purchaseCost'],
            'licensedToName'   => $faker['orderNumber'],
            'licensedToEmail'  => $faker['notes'],
            'licenseName'      => $faker['licenseName'],
            'manufacturerName' => $faker['category'],
            'notes'            => $faker['companyName'],
            'orderNumber'      => $faker['expirationDate'],
            'purchaseCost'     => $faker['isMaintained'],
            'purchaseDate'     => $faker['isReassignAble'],
            'seats'            => $faker['licensedToName'],
            'serialNumber'     => $faker['licensedToEmail'],
            'supplierName'     => $faker['manufacturerName']
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->license()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Category'         => 'supplier',
                'Company'          => 'serial',
                'expiration date'  => 'seats',
                'maintained'       => 'purchase_date',
                'reassignable'     => 'purchase_cost',
                'Licensed To Name' => 'order_number',
                'Licensed To Email' => 'notes',
                'licenseName'      => 'name',
                'manufacturer'     => 'category',
                'Notes'            => 'company',
                'Serial number'    => 'license_email',
                'Order Number'     => 'expiration_date',
                'purchase Cost'    => 'maintained',
                'purchase Date'    => 'reassignable',
                'seats'            => 'license_name',
                'supplier'         => 'manufacturer'
            ]
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
}
