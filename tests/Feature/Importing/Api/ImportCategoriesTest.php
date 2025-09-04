<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Category;
use App\Models\User;
use App\Models\Import;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\CategoriesImportFileBuilder as ImportFileBuilder;

class ImportCategoriesTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'category';
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
    public function importCategory(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->categories()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'send-welcome' => 0])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('categories.index')]
            ]);

        $newCategory = Category::query()
            ->where('name', $row['name'])
            ->sole();

        $this->assertEquals($row['name'], $newCategory->name);

    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->categories()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }


    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['name' => '']);
        $import = Import::factory()->categories()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    '' => [
                        'Category ""' => [
                            'name' =>
                                ['The name field is required.'],
                        ],
                    ]

                ]
            ]);

        $newCategory = Category::query()
            ->where('name', $importFileBuilder->firstRow()['name'])
            ->get();

        $this->assertCount(0, $newCategory);
    }


    #[Test]
    public function updateCategoryFromImport(): void
    {
        $category = Category::factory()->create()->refresh();
        $importFileBuilder = ImportFileBuilder::new(['name' => $category->name, 'category_type' => 'asset', 'notes' => $category->notes, 'use_default_eula' => 0, 'require_acceptance' => 0, 'checkin_email' => 0]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->categories()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedCategory = Category::query()->find($category->id);
        $updatedAttributes = [
            'name',
        ];

        $this->assertEquals($row['name'], $updatedCategory->name);

        $this->assertEquals(
            Arr::except($category->attributesToArray(), array_merge($updatedAttributes, $category->getDates())),
            Arr::except($updatedCategory->attributesToArray(), array_merge($updatedAttributes, $category->getDates())),
        );
    }

}
