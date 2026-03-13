<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Manufacturer;
use App\Models\User;
use App\Models\Import;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\ManufacturersImportFileBuilder as ImportFileBuilder;

class ImportManufacturersTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'manufacturer';
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
    public function importManufacturer(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->manufacturers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'send-welcome' => 0])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('manufacturers.index')]
            ]);

        $newManufacturer = Manufacturer::query()
            ->where('name', $row['name'])
            ->sole();

        $this->assertEquals($row['name'], $newManufacturer->name);

    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->manufacturers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }


    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['name' => '']);
        $import = Import::factory()->manufacturers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    '' => [
                        'Manufacturer ""' => [
                            'name' =>
                                ['The name field is required.'],
                        ],
                    ]

                ]
            ]);

        $newManufacturer = Manufacturer::query()
            ->where('name', $importFileBuilder->firstRow()['name'])
            ->get();

        $this->assertCount(0, $newManufacturer);
    }


    #[Test]
    public function updateManufacturerFromImport(): void
    {
        $manufacturer = Manufacturer::factory()->create()->refresh();
        $importFileBuilder = ImportFileBuilder::new(['name' => $manufacturer->name, 'support_url' => $manufacturer->support_url, 'support_phone' => $manufacturer->support_phone, 'support_email' => $manufacturer->support_email]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->manufacturers()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedManufacturer = Manufacturer::query()->find($manufacturer->id);
        $updatedAttributes = [
            'name',
            'support_url',
            'support_phone',
            'support_email',
        ];

        $this->assertEquals($row['name'], $updatedManufacturer->name);

        $this->assertEquals(
            Arr::except($manufacturer->attributesToArray(), array_merge($updatedAttributes, $manufacturer->getDates())),
            Arr::except($updatedManufacturer->attributesToArray(), array_merge($updatedAttributes, $manufacturer->getDates())),
        );
    }

}
