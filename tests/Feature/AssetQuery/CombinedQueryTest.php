<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use Tests\TestCase;


class CombinedQueryTest extends TestCase
{
    public function testFilterAssetModelLocation()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        // Assets
        $modelA_locationA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $modelA_locationB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $modelB_locationA = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $modelB_locationB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);

        $filter = ['model' => $modelA->name, 'location' => $locationB->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($modelA_locationB));
        $this->assertFalse($results->contains($modelA_locationA));
        $this->assertFalse($results->contains($modelB_locationA));
        $this->assertFalse($results->contains($modelB_locationB));
    }

    public function testFilterAssetModelLocationArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();

        // Assets
        $modelA_locationA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $modelA_locationB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $modelA_locationC = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationC->id]);
        $modelB_locationA = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $modelB_locationB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);
        $modelB_locationC = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationC->id]);

        $filter = ['model' => $modelB->name, 'location' => [$locationB->name, $locationA->name]];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($modelB_locationA));
        $this->assertTrue($results->contains($modelB_locationB));
        $this->assertFalse($results->contains($modelA_locationA));
        $this->assertFalse($results->contains($modelA_locationB));
        $this->assertFalse($results->contains($modelA_locationC));
        $this->assertFalse($results->contains($modelB_locationC));

    }

    public function testFilterAssetModelStatus()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $modelA_statusA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $modelA_statusB = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $modelA_statusA = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);
        $modelA_statusA = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusB->id]);

        $filter = ['model' => $modelA->name, 'status_label' => $statusB->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($modelA_statusB));
        $this->assertFalse($results->contains($modelA_statusA));
        $this->assertFalse($results->contains($modelA_statusA));
        $this->assertFalse($results->contains($modelA_statusA));

    }

    public function testFilterAssetModelStatusArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();
        $statusC = Statuslabel::factory()->create();

        $modelA_statusA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $modelA_statusB = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $modelA_statusC = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusC->id]);
        $modelB_statusA = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);

        $filter = ['model' => $modelA->name, 'status_label' => [$statusA->name, $statusB->name]];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($modelA_statusA));
        $this->assertTrue($results->contains($modelA_statusB));
        $this->assertFalse($results->contains($modelA_statusC));
        $this->assertFalse($results->contains($modelB_statusA));
    }


    public function testFilterAssetModelManufacturer()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA_manufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelC_manufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelC_manufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelD_manufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $asset_modelA_manufacturerA = Asset::factory()->create(['model_id' => $modelA_manufacturerA->id]);
        $asset_modelB_manufacturerA = Asset::factory()->create(['model_id' => $modelC_manufacturerA->id]);
        $asset_modelC_manufacturerB = Asset::factory()->create(['model_id' => $modelC_manufacturerB->id]);
        $asset_modelD_manufacturerB = Asset::factory()->create(['model_id' => $modelD_manufacturerB->id]);

        $filter = ['model' => $modelA_manufacturerA->name, 'manufacturer' => $manufacturerA->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($asset_modelA_manufacturerA));
        $this->assertFalse($results->contains($asset_modelB_manufacturerA));
        $this->assertFalse($results->contains($asset_modelC_manufacturerB));
        $this->assertFalse($results->contains($asset_modelD_manufacturerB));
    }


    public function testFilterAssetModelManufacturerArray()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA_manufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB_manufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelC_manufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelD_manufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $asset_modelA_manufacturerA = Asset::factory()->create(['model_id' => $modelA_manufacturerA->id]);
        $asset_modelB_manufacturerA = Asset::factory()->create(['model_id' => $modelB_manufacturerA->id]);
        $asset_modelC_manufacturerB = Asset::factory()->create(['model_id' => $modelC_manufacturerB->id]);
        $asset_modelD_manufacturerB = Asset::factory()->create(['model_id' => $modelD_manufacturerB->id]);

        $filter = ['model' => [$modelA_manufacturerA->name, $modelC_manufacturerB->name], 'manufacturer' => [$manufacturerA->name, $manufacturerB->name]];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($asset_modelA_manufacturerA));
        $this->assertTrue($results->contains($asset_modelC_manufacturerB));
        $this->assertFalse($results->contains($asset_modelB_manufacturerA));
        $this->assertFalse($results->contains($asset_modelD_manufacturerB));
    }


    public function testFilterAssetLocationStatus()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create(['location_id' => $locationA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationA->id, 'status_id' => $statusB->id]);
        $assetC = Asset::factory()->create(['location_id' => $locationB->id, 'status_id' => $statusA->id]);
        $assetD = Asset::factory()->create(['location_id' => $locationB->id, 'status_id' => $statusB->id]);

        $filter = ['location' => $locationA->name, 'status_label' => $statusB->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

    public function testFilterAssetLocationArrayStatus()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();

        $statusA = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create(['location_id' => $locationA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id, 'status_id' => $statusA->id]);
        $assetC = Asset::factory()->create(['location_id' => $locationC->id, 'status_id' => $statusA->id]);
        $assetD = Asset::factory()->create(['location_id' => $locationB->id, 'status_id' => $statusA->id]);

        $filter = ['location' => [$locationA->name, $locationB->name], 'status_label' => $statusA->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetC));
    }

    public function testFilterAssetLocationManufacturer()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);

        $filter = ['location' => $locationA->name, 'manufacturer' => $manufacturerA->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

    public function testFilterAssetLocationArrayManufacturerArray()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelC = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelD = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id, 'location_id' => $locationB->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id, 'location_id' => $locationB->id]);

        $filter = [
            'location' => [$locationA->name, $locationB->name],
            'manufacturer' => [$manufacturerA->name, $manufacturerB->name]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(4, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
    }

    public function testFilterAssetStatusManufacturer()
    {
        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusB->id]);

        $filter = ['status_label' => $statusA->name, 'manufacturer' => $manufacturerA->name];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

    public function testFilterAssetStatusArrayManufacturerArray()
    {
        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelC = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelD = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id, 'status_id' => $statusB->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id, 'status_id' => $statusB->id]);

        $filter = [
            'status_label' => [$statusA->name, $statusB->name],
            'manufacturer' => [$manufacturerA->name, $manufacturerB->name]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(4, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
    }

    public function testFilterAssetModelLocationStatus()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id, 'status_id' => $statusA->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id, 'status_id' => $statusB->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id, 'status_id' => $statusB->id]);

        $filter = [
            'model' => $modelA->name,
            'location' => $locationB->name,
            'status_label' => $statusA->name
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

    public function testFilterAssetModelLocationArrayStatusArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id, 'status_id' => $statusB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id, 'status_id' => $statusA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id, 'status_id' => $statusB->id]);

        $filter = [
            'model' => $modelA->name,
            'location' => [$locationA->name, $locationB->name],
            'status_label' => [$statusA->name, $statusB->name]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

    public function testFilterAssetModelLocationManufacturer()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);

        $filter = [
            'model' => $modelA->name,
            'location' => $locationA->name,
            'manufacturer' => $manufacturerA->name
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }
    public function testFilterAssetModelLocationArrayManufacturerArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);

        $filter = [
            'model' => [$modelA->name, $modelB->name],
            'location' => [$locationA->name, $locationB->name],
            'manufacturer' => [$manufacturerA->name, $manufacturerB->name]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(4, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
    }

    public function testFilterAssetModelStatusManufacturer()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusB->id]);

        $filter = [
            'model' => $modelA->name,
            'status_label' => $statusA->name,
            'manufacturer' => $manufacturerA->name
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

    public function testFilterAssetModelStatusArrayManufacturerArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusB->id]);

        $filter = [
            'model' => [$modelA->name, $modelB->name],
            'status_label' => [$statusA->name, $statusB->name],
            'manufacturer' => [$manufacturerA->name, $manufacturerB->name]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(4, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
    }

    // Edge cases:

    public function testFilterAssetNoFiltersReturnsAll()
    {
        $assetA = Asset::factory()->create();
        $assetB = Asset::factory()->create();
        $assetC = Asset::factory()->create();

        // No filters applied
        $filter = [];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
    }

    public function testFilterAssetAllFiltersAsStrings()
    {
        $model = AssetModel::factory()->create();
        $location = Location::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $status = Statuslabel::factory()->create();

        $model->manufacturer_id = $manufacturer->id;
        $model->save();

        $assetA = Asset::factory()->create([
            'model_id' => $model->id,
            'location_id' => $location->id,
            'status_id' => $status->id
        ]);
        $assetB = Asset::factory()->create(); // Should not match

        $filter = [
            'model' => $model->name,
            'location' => $location->name,
            'manufacturer' => $manufacturer->name,
            'status_label' => $status->name,
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAllFiltersAsArrays()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create([
            'model_id' => $modelA->id,
            'location_id' => $locationA->id,
            'status_id' => $statusA->id
        ]);
        $assetB = Asset::factory()->create([
            'model_id' => $modelB->id,
            'location_id' => $locationB->id,
            'status_id' => $statusB->id
        ]);
        $assetC = Asset::factory()->create(); // Should not match

        $filter = [
            'model' => [$modelA->name, $modelB->name],
            'location' => [$locationA->name, $locationB->name],
            'manufacturer' => [$manufacturerA->name, $manufacturerB->name],
            'status_label' => [$statusA->name, $statusB->name],
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    public function testFilterWithEmptyArrayReturnsNone()
    {
        Asset::factory()->count(3)->create();
        $filter = ['model' => []];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
    }

    public function testFilterWithNonexistentValueReturnsNone()
    {
        Asset::factory()->count(3)->create();
        $filter = ['status_label' => 'NonexistentStatus'];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
    }

    public function testFilterWithMixedValuesReturnsMatchingOnly()
    {
        $modelA = AssetModel::factory()->create();
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $filter = ['model' => [$modelA->name, 'NonexistentModel']];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterWithDuplicateValuesReturnsUniqueResults()
    {
        $modelA = AssetModel::factory()->create();
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $filter = ['model' => [$modelA->name, $modelA->name]];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterWithNullValueReturnsNone()
    {
        $numberOfAssets = 5;
        Asset::factory()->count($numberOfAssets)->create();
        $filter = ['location' => null];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount($numberOfAssets, $results);
    }

    public function testConflictingFiltersReturnNone()
    {
        $modelA = AssetModel::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();
        $modelA->manufacturer_id = $manufacturerB->id + 1; // Not matching
        $modelA->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $filter = ['model' => $modelA->name, 'manufacturer' => 'NonexistentManufacturer'];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
    }

    public function testLargeArrayOfModelsReturnsAllMatching()
    {
        $models = AssetModel::factory()->count(50)->create();
        foreach ($models as $model) {
            Asset::factory()->create(['model_id' => $model->id]);
        }
        $filter = ['model' => $models->pluck('name')->toArray()];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(50, $results);
    }

    public function testCombinationOfArrayAndStringFilters()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $filter = [
            'model' => [$modelA->name, $modelB->name],
            'location' => $locationA->name
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testOverlappingFiltersReturnsAllMatches()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $filter = [
            'manufacturer' => $manufacturerA->name,
            'model' => [$modelA->name, $modelB->name]
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterWithSpecialCharacters()
    {
        $model = AssetModel::factory()->create(['name' => 'Mödel#1']);
        $asset = Asset::factory()->create(['model_id' => $model->id]);
        $filter = ['model' => 'Mödel#1'];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($asset));
    }

    public function testAssetsWithMissingForeignKey()
    {
        $locationA = Location::factory()->create();
        $assetWithLocation = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetWithoutLocation = Asset::factory()->create(['location_id' => null]);
        $filter = ['location' => $locationA->name];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertTrue($results->contains($assetWithLocation));
        $this->assertFalse($results->contains($assetWithoutLocation));
    }


}