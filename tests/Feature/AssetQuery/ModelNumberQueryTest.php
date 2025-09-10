<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use Tests\Support\GetExtendedPrefix;
use Tests\TestCase;


class ModelNumberQueryTest extends TestCase
{

    // Load trait
    use GetExtendedPrefix;

    public function testFilterAssetModelNumberEmptyString()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model_number' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }


    public function testFilterAssetModelNumberStringComplete()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model_number' => $modelA->model_number];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetModelNumberStringPartial()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $queryString = ModelNumberQueryTest::getExtendedPrefix($modelA->model_number, $modelB->model_number);
        $filter = ['model_number' => $queryString];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetModelNumberArraySingle()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model_number' => [$modelA->model_number]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetModelNumberArrayMultiple()
    {

        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $modelC = AssetModel::factory()->create();
        $modelD = AssetModel::factory()->create();
        $modelE = AssetModel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id]);
        $assetE = Asset::factory()->create(['model_id' => $modelE->id]);

        // When: Query with an array of names
        $filter = ['model_number' => [$modelB->model_number, $modelE->model_number]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }
}