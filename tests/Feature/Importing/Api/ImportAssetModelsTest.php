<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Category;
use App\Models\AssetModel;
use App\Models\User;
use App\Models\Import;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\AssetModelsImportFileBuilder as ImportFileBuilder;

class ImportAssetModelsTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'assetModel';
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
    public function importAssetModels(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->assetmodel()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'send-welcome' => 0])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('models.index')]
            ]);

        $newAssetModel = AssetModel::query()
            ->with(['category'])
            ->where('name', $row['name'])
            ->sole();

        $this->assertEquals($row['name'], $newAssetModel->name);
        $this->assertEquals($row['model_number'], $newAssetModel->model_number);

    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->assetmodel()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }


    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['name' => '']);
        $import = Import::factory()->assetmodel()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    '' => [
                        'name' => [
                            'name' =>
                                ['The name field is required.'],
                        ],
                    ]
                ]
            ]);

        $newAssetModels = AssetModel::query()
            ->where('name', $importFileBuilder->firstRow()['name'])
            ->get();

        $this->assertCount(0, $newAssetModels);
    }


    #[Test]
    public function updateAssetModelFromImport(): void
    {
        $assetmodel = AssetModel::factory()->create();
        $category = Category::find($assetmodel->category_id);
        $importFileBuilder = ImportFileBuilder::new(['name' => $assetmodel->name, 'model_number' => Str::random(), 'category' => $category->name]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->assetmodel()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('models.index')]
            ]);

        $updatedAssetmodel = AssetModel::query()->find($assetmodel->id);

        $this->assertEquals($row['model_number'], $updatedAssetmodel->model_number);
        $this->assertEquals($row['name'], $updatedAssetmodel->name);

    }


}
