<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Manufacturer;
use Tests\Support\GetExtendedPrefix;
use Tests\TestCase;


class ManufacturerQueryTest extends TestCase
{

        // Load trait
    use GetExtendedPrefix;

    public function testFilterAssetManufacturerEmptyString()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }


    public function testFilterAssetManufacturerStringComplete()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => $manufacturerA->name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetManufacturerStringPartial()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $queryString = ManufacturerQueryTest::getExtendedPrefix($manufacturerA->name, $manufacturerB->name);
        $filter = ['manufacturer' => $queryString];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetManufacturerArraySingle()
    {

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => [$manufacturerA->name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetManufacturerArrayMultiple()
    {

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();
        $manufacturerC = Manufacturer::factory()->create();
        $manufacturerD = Manufacturer::factory()->create();
        $manufacturerE = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelC = AssetModel::factory()->create(['manufacturer_id' => $manufacturerC->id]);
        $modelD = AssetModel::factory()->create(['manufacturer_id' => $manufacturerD->id]);
        $modelE = AssetModel::factory()->create(['manufacturer_id' => $manufacturerE->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id]);
        $assetE = Asset::factory()->create(['model_id' => $modelE->id]);

        // When: Query with an array of names
        $filter = ['manufacturer' => [$manufacturerB->name, $manufacturerE->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }

    public function testFilterAssetManufacturerId()
    {

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => $manufacturerA->id];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetManufacturerIdArraySingle()
    {

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => [$manufacturerA->id]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetManufacturerIdAndNameArray()
    {

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();
        $manufacturerC = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelC = AssetModel::factory()->create(['manufacturer_id' => $manufacturerC->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = ['manufacturer' => [$manufacturerA->id, $manufacturerB->name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));

    }
}