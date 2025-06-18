<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Supplier;
use App\Models\User;
use App\Models\Import;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\SuppliersImportFileBuilder as ImportFileBuilder;

class ImportSuppliersTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'supplier';
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
    public function importSupplier(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->suppliers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'send-welcome' => 0])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('suppliers.index')]
            ]);

        $newSupplier = Supplier::query()
            ->where('name', $row['name'])
            ->sole();

        $this->assertEquals($row['name'], $newSupplier->name);

    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->suppliers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }


    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['name' => '']);
        $import = Import::factory()->suppliers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    '' => [
                        'Supplier ""' => [
                            'name' =>
                                ['The name field is required.'],
                        ],
                    ]

                ]
            ]);

        $newSupplier = Supplier::query()
            ->where('name', $importFileBuilder->firstRow()['name'])
            ->get();

        $this->assertCount(0, $newSupplier);
    }


    #[Test]
    public function updateSupplierFromImport(): void
    {
        $supplier = Supplier::factory()->create()->refresh();
        $importFileBuilder = ImportFileBuilder::new(['name' => $supplier->name, 'url' => $supplier->url, 'phone' => $supplier->phone, 'fax' => $supplier->fax, 'contact' => $supplier->contact, 'email' => $supplier->email]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->suppliers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedSupplier = Supplier::query()->find($supplier->id);
        $updatedAttributes = [
            'name',
            'url',
            'phone',
            'fax',
            'contact',
            'email',
        ];

        $this->assertEquals($row['name'], $updatedSupplier->name);

        $this->assertEquals(
            Arr::except($supplier->attributesToArray(), array_merge($updatedAttributes, $supplier->getDates())),
            Arr::except($updatedSupplier->attributesToArray(), array_merge($updatedAttributes, $supplier->getDates())),
        );
    }

}
