<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use Tests\TestCase;


class ModelQueryTest extends TestCase
{
    public function testFilterAssetModelEmptyString()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }


    public function testFilterAssetModelStringComplete()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model' => $modelA->name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetModelStringPartial()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $queryString = substr($modelA->name, 0, floor(strlen($modelA->name) / 2));
        $filter = ['model' => $queryString];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetModelArraySingle()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model' => [$modelA->name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetModelArrayMultiple()
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
        $filter = ['model' => [$modelB->name, $modelE->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }

    public function testFilterAssetModelId()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        // When: Query with an array of names
        $filter = ['model' => [$modelB->id]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetA));
    }

    public function testFilterAssetModelIdArraySingle()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        // When: Query with an array of names
        $filter = ['model' => [$modelB->id]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetA));
    }

    public function testFilterAssetModelIdAndNameArray()
    {

        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $modelC = AssetModel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        // When: Query with an array of names
        $filter = ['model' => [$modelA->name, $modelB->id]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

}