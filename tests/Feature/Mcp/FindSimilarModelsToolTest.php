<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\FindSimilarModelsTool;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class FindSimilarModelsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssetModels()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return resolve(FindSimilarModelsTool::class)->handle(new Request($args));
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_model_not_found()
    {
        $this->assertTrue($this->handle(['model_id' => 999999])->responses()->first()->isError());
    }

    public function test_same_category_scores_higher_than_same_manufacturer_alone()
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $manufacturer = Manufacturer::factory()->create();

        $source = AssetModel::factory()->create([
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'name' => 'Source Model',
        ]);
        $sameCategory = AssetModel::factory()->create([
            'category_id' => $category->id,
            'manufacturer_id' => Manufacturer::factory()->create()->id,
            'name' => 'Different Manufacturer Same Category',
        ]);
        $sameManufacturer = AssetModel::factory()->create([
            'category_id' => $otherCategory->id,
            'manufacturer_id' => $manufacturer->id,
            'name' => 'Different Category Same Manufacturer',
        ]);

        $content = $this->handle(['model_id' => $source->id])->getStructuredContent();

        $sameCategoryRow = collect($content['candidates'])->firstWhere('id', $sameCategory->id);
        $sameManufacturerRow = collect($content['candidates'])->firstWhere('id', $sameManufacturer->id);

        $this->assertGreaterThan($sameManufacturerRow['score'], $sameCategoryRow['score']);
    }

    public function test_name_token_overlap_contributes_to_score()
    {
        $category = Category::factory()->create();

        $source = AssetModel::factory()->create([
            'category_id' => $category->id,
            'name' => 'MacBook Pro 14-inch M3',
        ]);
        $overlap = AssetModel::factory()->create([
            'category_id' => $category->id,
            'name' => 'MacBook Pro 16-inch M3',
        ]);
        $noOverlap = AssetModel::factory()->create([
            'category_id' => $category->id,
            'name' => 'Something Completely Unrelated',
        ]);

        $content = $this->handle(['model_id' => $source->id])->getStructuredContent();

        $overlapRow = collect($content['candidates'])->firstWhere('id', $overlap->id);
        $noOverlapRow = collect($content['candidates'])->firstWhere('id', $noOverlap->id);

        $this->assertGreaterThan($noOverlapRow['score'], $overlapRow['score']);
    }

    public function test_has_stock_bonus_is_applied()
    {
        $category = Category::factory()->create();

        $source = AssetModel::factory()->create(['category_id' => $category->id]);
        $withStock = AssetModel::factory()->create(['category_id' => $category->id]);
        Asset::factory()->count(2)->create(['model_id' => $withStock->id, 'assigned_to' => null]);

        $content = $this->handle(['model_id' => $source->id])->getStructuredContent();

        $row = collect($content['candidates'])->firstWhere('id', $withStock->id);

        $this->assertContains('has_stock', $row['reasons']);
        $this->assertSame(2, $row['available_count']);
    }

    public function test_require_available_inventory_excludes_out_of_stock_candidates()
    {
        $category = Category::factory()->create();

        $source = AssetModel::factory()->create(['category_id' => $category->id]);
        $outOfStock = AssetModel::factory()->create(['category_id' => $category->id]);
        $withStock = AssetModel::factory()->create(['category_id' => $category->id]);
        Asset::factory()->create(['model_id' => $withStock->id, 'assigned_to' => null]);

        $content = $this->handle([
            'model_id' => $source->id,
            'require_available_inventory' => true,
        ])->getStructuredContent();

        $returnedIds = array_column($content['candidates'], 'id');
        $this->assertContains($withStock->id, $returnedIds);
        $this->assertNotContains($outOfStock->id, $returnedIds);
    }

    public function test_source_model_is_excluded_from_candidates()
    {
        $category = Category::factory()->create();
        $source = AssetModel::factory()->create(['category_id' => $category->id]);

        $content = $this->handle(['model_id' => $source->id])->getStructuredContent();

        $returnedIds = array_column($content['candidates'], 'id');
        $this->assertNotContains($source->id, $returnedIds);
    }

    public function test_limit_parameter_caps_returned_candidates()
    {
        $category = Category::factory()->create();
        $source = AssetModel::factory()->create(['category_id' => $category->id]);
        AssetModel::factory()->count(5)->create(['category_id' => $category->id]);

        $content = $this->handle([
            'model_id' => $source->id,
            'limit' => 2,
        ])->getStructuredContent();

        $this->assertLessThanOrEqual(2, count($content['candidates']));
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $source = AssetModel::factory()->create();

        $this->assertTrue($this->handle(['model_id' => $source->id])->responses()->first()->isError());
    }
}
