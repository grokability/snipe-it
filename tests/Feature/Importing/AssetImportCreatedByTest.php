<?php

namespace Tests\Feature\Importing;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Import;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

/**
 * Regression coverage for the "assets imported via CSV have blank created_by"
 * bug. ItemImporter::handle() populated $this->item['created_by'] with
 * auth()->id(), but sanitizeItemForStoring() then filtered the payload
 * through $model->getFillable(), which does not include created_by on the
 * Asset model. Every other importer (Category, Location, License, Supplier,
 * User, etc.) worked around this by setting $model->created_by = auth()->id()
 * directly on the model. Asset was the odd one out, and every asset created
 * via the importer ended up with a null created_by column.
 */
class AssetImportCreatedByTest extends TestCase
{
    use CleansUpImportFiles;

    #[Test]
    public function new_asset_created_via_import_records_the_importing_users_id_as_created_by(): void
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        Company::factory()->create();
        AssetModel::factory()->for($category, 'category')->create(['name' => 'Import Test Model']);

        $importer = User::factory()->canImport()->create();

        $csv = "asset tag,item name,category,status,model name\n";
        $csv .= "CREATEDBY-001,Import Created Asset,{$category->name},{$statusLabel->name},Import Test Model\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('createdby.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                    'model name' => 'asset_model',
                ],
            ])
            ->assertSuccessful();

        $imported = Asset::where('asset_tag', 'CREATEDBY-001')->firstOrFail();

        $this->assertSame(
            $importer->id,
            $imported->created_by,
            'AssetImporter must set created_by to the id of the user running the import.'
        );
    }

    #[Test]
    public function updating_an_existing_asset_via_import_does_not_overwrite_created_by(): void
    {
        // Update path uses sanitizeItemForStoring(updating: true) which rejects
        // empty values, so a null created_by from the item array cannot silently
        // clobber the existing value. Pin the intended non-behavior explicitly
        // so a future refactor cannot introduce a regression that would
        // reassign every updated asset's created_by to the importer.
        $category = Category::factory()->create(['category_type' => 'asset']);
        $location = Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        $company = Company::factory()->create();
        $assetModel = AssetModel::factory()->for($category, 'category')->create();

        $originalOwner = User::factory()->create();

        $asset = Asset::factory()
            ->for($assetModel, 'model')
            ->for($statusLabel, 'status')
            ->for($location, 'defaultLoc')
            ->for($company, 'company')
            ->create([
                'asset_tag' => 'CREATEDBY-002',
                'name' => 'Existing Asset',
                'created_by' => $originalOwner->id,
            ]);

        $importer = User::factory()->canImport()->create();

        $csv = "asset tag,item name,category,status\n";
        $csv .= "CREATEDBY-002,Renamed Via Import,{$category->name},{$statusLabel->name}\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('createdby-update.csv', $csv),
                ],
            ])
            ->assertSuccessful();

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

        $asset->refresh();

        $this->assertSame(
            $originalOwner->id,
            $asset->created_by,
            'Import updates must not overwrite the existing created_by on an existing asset.'
        );
        $this->assertSame('Renamed Via Import', $asset->name, 'Sanity check: the update should still take effect on other fields.');
    }

    #[Test]
    public function auto_created_checkout_target_user_records_created_by(): void
    {
        // Snipe-IT auto-creates the target user when an asset import row
        // references a username that doesn't exist yet. Before the fix,
        // that new user landed with created_by = null, matching the same
        // omission on the asset itself.
        $category = Category::factory()->create(['category_type' => 'asset']);
        Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        Company::factory()->create();
        AssetModel::factory()->for($category, 'category')->create(['name' => 'Auto User Test Model']);

        $importer = User::factory()->canImport()->create();

        $csv = "asset tag,item name,category,status,model name,full name,username\n";
        $csv .= "CREATEDBY-003,Import Asset Checkout,{$category->name},{$statusLabel->name},Auto User Test Model,Import Target User,import_target_user\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('createdby-user.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                    'model name' => 'asset_model',
                    'full name' => 'full_name',
                    'username' => 'username',
                ],
            ])
            ->assertSuccessful();

        $autoCreatedUser = User::where('username', 'import_target_user')->firstOrFail();

        $this->assertSame(
            $importer->id,
            $autoCreatedUser->created_by,
            'A user auto-created as the checkout target during an asset import must record the importing user as its creator.'
        );
    }

    #[Test]
    public function auto_created_department_records_created_by_and_attaches_to_the_auto_created_user(): void
    {
        // Two side-effects tied together: the department gets created
        // (previously with null created_by), and its id gets propagated
        // through to the auto-created user (previously dropped on the
        // floor because createOrFetchUser hard-coded department_id to '').
        $category = Category::factory()->create(['category_type' => 'asset']);
        Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        Company::factory()->create();
        AssetModel::factory()->for($category, 'category')->create(['name' => 'Auto Dept Test Model']);

        $importer = User::factory()->canImport()->create();

        $csv = "asset tag,item name,category,status,model name,full name,username,department\n";
        $csv .= "CREATEDBY-004,Import Dept Asset,{$category->name},{$statusLabel->name},Auto Dept Test Model,Dept Target User,dept_target_user,Import Test Department\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('createdby-dept.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                    'model name' => 'asset_model',
                    'full name' => 'full_name',
                    'username' => 'username',
                    'department' => 'department',
                ],
            ])
            ->assertSuccessful();

        $autoCreatedDept = Department::where('name', 'Import Test Department')->firstOrFail();
        $this->assertSame(
            $importer->id,
            $autoCreatedDept->created_by,
            'A department auto-created during asset import must record the importing user as its creator.'
        );

        $autoCreatedUser = User::where('username', 'dept_target_user')->firstOrFail();
        $this->assertSame(
            $autoCreatedDept->id,
            $autoCreatedUser->department_id,
            'The department created for the CSV row must be linked to the auto-created checkout-target user (department propagation).'
        );
    }
}
