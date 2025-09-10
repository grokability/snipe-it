<?php
namespace Tests\Unit;

use App\Models\Category;
use App\Models\Asset;
use App\Models\AssetModel;
use Tests\Support\GetExtendedPrefix;
use Tests\TestCase;


class CategoryQueryTest extends TestCase
{

        // Load trait
    use GetExtendedPrefix;

    public function testFilterAssetCategoryEmptyString()
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }


    public function testFilterAssetCategoryStringComplete()
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => $categoryA->name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetCategoryStringPartial()
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $queryString = CategoryQueryTest::getExtendedPrefix($categoryA->name, $categoryB->name);
        $filter = ['category' => $queryString];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetCategoryArraySingle()
    {

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => [$categoryA->name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetCategoryArrayMultiple()
    {

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $categoryC = Category::factory()->create();
        $categoryD = Category::factory()->create();
        $categoryE = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);
        $modelC = AssetModel::factory()->create(['category_id' => $categoryC->id]);
        $modelD = AssetModel::factory()->create(['category_id' => $categoryD->id]);
        $modelE = AssetModel::factory()->create(['category_id' => $categoryE->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id]);
        $assetE = Asset::factory()->create(['model_id' => $modelE->id]);

        // When: Query with an array of names
        $filter = ['category' => [$categoryB->name, $categoryE->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }

    public function testFilterAssetAssignedToCategoryId()
    {

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => $categoryA->id];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetAssignedToCategoryIdArraySingle()
    {

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => [$categoryA->id]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetAssignedToCategoryIdAndNameArray()
    {

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $categoryC = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);
        $modelC = AssetModel::factory()->create(['category_id' => $categoryC->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = ['category' => [$categoryA->id, $categoryB->name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));

    }
}