<?php

namespace Tests\Feature\Importing;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Import;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

class AssetImportDoNotImportMappingTest extends TestCase
{
    use CleansUpImportFiles;

    #[Test]
    public function do_not_import_column_mapped_as_null_does_not_block_the_import()
    {
        [$asset, $category, $statusLabel, $importer] = $this->seedAssetForUpdate();

        $csv = "asset tag,item name,category,status,extra_notes\n";
        $csv .= "TEST-DNI-001,Renamed Asset,{$category->name},{$statusLabel->name},ignored value\n";

        $this->uploadAndProcess($importer, $csv, [
            'import-type' => 'asset',
            'import-update' => true,
            'column-mappings' => [
                'asset tag' => 'asset_tag',
                'item name' => 'item_name',
                'category' => 'category',
                'status' => 'status',
                'extra_notes' => null,
            ],
        ]);

        $asset->refresh();
        $this->assertSame(
            'Renamed Asset',
            $asset->name,
            'The import must still apply mapped columns when a sibling column is null-mapped.',
        );
        $this->assertSame(
            'Original notes',
            $asset->notes,
            'The "Do not import" column value must not overwrite an unrelated DB column.',
        );
    }

    /**
     * @return array{0: Asset, 1: Category, 2: Statuslabel, 3: User}
     */
    private function seedAssetForUpdate(): array
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $location = Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        $company = Company::factory()->create();
        $assetModel = AssetModel::factory()->for($category, 'category')->create();

        $asset = Asset::factory()
            ->for($assetModel, 'model')
            ->for($statusLabel, 'status')
            ->for($location, 'defaultLoc')
            ->for($company, 'company')
            ->create([
                'asset_tag' => 'TEST-DNI-001',
                'name' => 'Original Asset',
                'notes' => 'Original notes',
            ]);

        $importer = User::factory()->canImport()->create();

        return [$asset, $category, $statusLabel, $importer];
    }

    private function uploadAndProcess(User $actor, string $csv, array $processPayload): void
    {
        $this->actingAsForApi($actor)
            ->postJson(route('api.imports.store'), [
                'files' => [$this->tempUploadedFile('test.csv', $csv)],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($actor)
            ->postJson(route('api.imports.importFile', $import->id), $processPayload)
            ->assertSuccessful();
    }
}
