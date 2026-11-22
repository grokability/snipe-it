<?php

namespace Tests\Feature\Importing;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Import;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

/**
 * Regression for issue #20108. The CSV importer's `DB::transaction`
 * wrap around the whole loop queued every row's
 * AssetObserver::created afterCommit callback until the batch
 * commit, so `settings.next_auto_tag_base` never advanced between
 * rows. Every row after the first read the same base and collided
 * on asset tag. Fix removes the outer batch transaction so each
 * row's afterCommit fires between iterations.
 */
class AssetImportAutoIncrementTest extends TestCase
{
    use CleansUpImportFiles;

    #[Test]
    public function multi_row_import_with_auto_increment_assigns_distinct_asset_tags(): void
    {
        $this->settings->enableAutoIncrement();

        Category::factory()->create(['category_type' => 'asset', 'name' => 'Auto-Inc Test Category']);
        Statuslabel::factory()->create(['name' => 'Auto-Inc Test Status']);
        Company::factory()->create();
        AssetModel::factory()
            ->for(Category::where('name', 'Auto-Inc Test Category')->first(), 'category')
            ->create(['name' => 'Auto-Inc Test Model']);

        // Three rows, no `asset tag` column, auto-increment on. Each
        // row should get the next tag in sequence. Before the fix,
        // row 2 collided on row 1's tag because the afterCommit
        // callback that advances next_auto_tag_base was still queued
        // waiting on the outer transaction to commit.
        $csv = "item name,category,status,model name\n"
            ."Auto-Inc Asset 1,Auto-Inc Test Category,Auto-Inc Test Status,Auto-Inc Test Model\n"
            ."Auto-Inc Asset 2,Auto-Inc Test Category,Auto-Inc Test Status,Auto-Inc Test Model\n"
            ."Auto-Inc Asset 3,Auto-Inc Test Category,Auto-Inc Test Status,Auto-Inc Test Model\n";

        $importer = User::factory()->canImport()->create();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('auto-inc.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'column-mappings' => [
                    'item name' => 'item_name',
                    'category' => 'category',
                    'status' => 'status',
                    'model name' => 'asset_model',
                ],
            ])
            ->assertSuccessful();

        $tags = Asset::whereIn('name', ['Auto-Inc Asset 1', 'Auto-Inc Asset 2', 'Auto-Inc Asset 3'])
            ->pluck('asset_tag')
            ->all();

        $this->assertCount(3, $tags, 'All three rows should have produced an asset');
        $this->assertCount(3, array_unique($tags), 'Each imported asset must have a distinct auto-incremented tag');
    }
}
