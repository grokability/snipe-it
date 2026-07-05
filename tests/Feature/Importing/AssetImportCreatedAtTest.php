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
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetImportCreatedAtTest extends TestCase
{
    /**
     * Test that importing assets doesn't modify created_at timestamps on existing assets
     * This test addresses the reported bug where large imports caused random created_at changes
     */
    #[Test]
    public function existing_asset_created_at_not_modified_on_import_update()
    {
        // Create test data
        $category = Category::factory()->create(['category_type' => 'asset']);
        $location = Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        $company = Company::factory()->create();
        $assetModel = AssetModel::factory()->for($category, 'category')->create();

        // Create an existing asset with a known created_at date
        $originalCreatedAt = now()->subDays(30)->toDateTimeString();
        $asset = Asset::factory()
            ->for($assetModel, 'model')
            ->for($statusLabel, 'status')
            ->for($location, 'defaultLoc')
            ->for($company, 'company')
            ->create([
                'asset_tag' => 'TEST-001',
                'name' => 'Test Asset',
                'created_at' => $originalCreatedAt,
            ]);

        // Create CSV content that updates the existing asset
        $csv = "asset tag,item name,category,status\n";
        $csv .= "TEST-001,Test Asset Updated,{$category->name},{$statusLabel->name}\n";

        // Perform import with update flag. The same user has to upload and
        // process, because the process endpoint scopes to the file's uploader.
        $importer = User::factory()->canImport()->create();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->createFakeUploadedFile('test.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        // Import the file
        $import = Import::latest()->first();
        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                ],
            ])
            ->assertSuccessful();

        // Verify the asset's created_at timestamp wasn't modified
        $asset->refresh();
        $this->assertEquals(
            $originalCreatedAt,
            $asset->created_at->toDateTimeString(),
            'Asset created_at timestamp was modified during import update, which should not happen'
        );

        // Verify the asset was updated correctly
        $this->assertEquals('Test Asset Updated', $asset->name);
    }

    /**
     * Test that multiple successive imports don't cause timestamp drift
     */
    #[Test]
    public function successive_imports_maintain_created_at_consistency()
    {
        // Create test data
        $category = Category::factory()->create(['category_type' => 'asset']);
        $location = Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        $company = Company::factory()->create();
        $assetModel = AssetModel::factory()->for($category, 'category')->create();

        // Create multiple existing assets
        $assets = collect();
        for ($i = 1; $i <= 5; $i++) {
            $assets->push(Asset::factory()
                ->for($assetModel, 'model')
                ->for($statusLabel, 'status')
                ->for($location, 'defaultLoc')
                ->for($company, 'company')
                ->create([
                    'asset_tag' => "TEST-{$i}",
                    'created_at' => now()->subDays(30 - $i),
                ]));
        }

        $originalCreatedAts = $assets->mapWithKeys(fn ($asset) => [$asset->id => $asset->created_at->toDateTimeString()])->toArray();

        // Perform first import update
        $csv = "asset tag,item name,category,status\n";
        foreach ($assets as $asset) {
            $csv .= "{$asset->asset_tag},{$asset->name},{$category->name},{$statusLabel->name}\n";
        }

        // Same user has to upload and process, because the process endpoint
        // scopes to the file's uploader.
        $importer = User::factory()->canImport()->create();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->createFakeUploadedFile('test1.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import1 = Import::latest()->first();
        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import1->id), [
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                ],
            ])
            ->assertSuccessful();

        // Perform second import update
        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->createFakeUploadedFile('test2.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import2 = Import::latest()->first();
        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import2->id), [
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                ],
            ])
            ->assertSuccessful();

        // Verify all assets' created_at timestamps remain unchanged
        foreach ($assets as $asset) {
            $asset->refresh();
            $this->assertEquals(
                $originalCreatedAts[$asset->id],
                $asset->created_at->toDateTimeString(),
                "Asset {$asset->asset_tag} created_at changed between imports"
            );
        }
    }

    /**
     * Helper method to create a fake uploaded file
     */
    protected function createFakeUploadedFile(string $filename, string $content)
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $content);

        return new UploadedFile(
            $path,
            $filename,
            'text/csv',
            null,
            true
        );
    }
}
