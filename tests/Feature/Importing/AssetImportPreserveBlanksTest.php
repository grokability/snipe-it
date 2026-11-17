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

/**
 * Coverage for the "Preserve blank CSV cells on update" wizard opt-in.
 * Default behavior on an update-mode import is that a present-but-empty
 * CSV cell clears the corresponding DB column. When the importer passes
 * `import-preserve-blanks=1`, that clear-on-blank step is skipped so
 * blank cells keep whatever value is already in the DB.
 */
class AssetImportPreserveBlanksTest extends TestCase
{
    use CleansUpImportFiles;

    #[Test]
    public function default_update_clears_db_value_when_csv_cell_is_blank()
    {
        [$asset, $category, $statusLabel, $importer] = $this->seedAssetForUpdate();

        $csv = "asset tag,item name,category,status,asset_notes\n";
        $csv .= "TEST-001,Test Asset,{$category->name},{$statusLabel->name},\n";

        $this->uploadAndProcess($importer, $csv, [
            'import-type' => 'asset',
            'import-update' => true,
            'column-mappings' => [
                'asset tag' => 'asset_tag',
                'item name' => 'item_name',
                'category' => 'category',
                'status' => 'status',
                'asset_notes' => 'asset_notes',
            ],
        ]);

        $asset->refresh();
        $this->assertNull(
            $asset->notes,
            'A blank asset_notes cell should clear the DB value under default behavior.'
        );
    }

    #[Test]
    public function preserve_blanks_keeps_db_value_when_csv_cell_is_blank()
    {
        [$asset, $category, $statusLabel, $importer] = $this->seedAssetForUpdate();

        $csv = "asset tag,item name,category,status,asset_notes\n";
        $csv .= "TEST-001,Test Asset,{$category->name},{$statusLabel->name},\n";

        $this->uploadAndProcess($importer, $csv, [
            'import-type' => 'asset',
            'import-update' => true,
            'import-preserve-blanks' => true,
            'column-mappings' => [
                'asset tag' => 'asset_tag',
                'item name' => 'item_name',
                'category' => 'category',
                'status' => 'status',
                'asset_notes' => 'asset_notes',
            ],
        ]);

        $asset->refresh();
        $this->assertSame(
            'Original notes',
            $asset->notes,
            'A blank asset_notes cell should keep the existing DB value when preserve-blanks is on.'
        );
    }

    #[Test]
    public function preserve_blanks_still_updates_populated_cells()
    {
        [$asset, $category, $statusLabel, $importer] = $this->seedAssetForUpdate();

        $csv = "asset tag,item name,category,status,asset_notes\n";
        $csv .= "TEST-001,Renamed Asset,{$category->name},{$statusLabel->name},\n";

        $this->uploadAndProcess($importer, $csv, [
            'import-type' => 'asset',
            'import-update' => true,
            'import-preserve-blanks' => true,
            'column-mappings' => [
                'asset tag' => 'asset_tag',
                'item name' => 'item_name',
                'category' => 'category',
                'status' => 'status',
                'asset_notes' => 'asset_notes',
            ],
        ]);

        $asset->refresh();
        $this->assertSame('Renamed Asset', $asset->name, 'Populated cells must still update under preserve-blanks.');
        $this->assertSame('Original notes', $asset->notes, 'Blank asset_notes cell must be preserved alongside the name update.');
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
                'asset_tag' => 'TEST-001',
                'name' => 'Test Asset',
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
