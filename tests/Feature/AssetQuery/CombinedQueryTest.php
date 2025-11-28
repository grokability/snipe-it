<?php
namespace Tests\Feature\AssetQuery;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use Tests\TestCase;
use Log;

class CombinedQueryTest extends TestCase
{
    public function testFilterAssetANDModelLocation()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        // Assets
        $modelALocationA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $modelALocationB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $modelBLocationA = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $modelBLocationB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$locationB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($modelALocationB));
        $this->assertFalse($results->contains($modelALocationA));
        $this->assertFalse($results->contains($modelBLocationA));
        $this->assertFalse($results->contains($modelBLocationB));
    }

    public function testFilterAssetModelLocationArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();

        // Assets
        $modelALocationA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $modelALocationB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $modelALocationC = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationC->id]);
        $modelBLocationA = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $modelBLocationB = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);
        $modelBLocationC = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationC->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => [$modelB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ],[
                'field' => 'location',
                'value' => [$locationB->name,$locationA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($modelBLocationA));
        $this->assertTrue($results->contains($modelBLocationB));
        $this->assertFalse($results->contains($modelALocationA));
        $this->assertFalse($results->contains($modelALocationB));
        $this->assertFalse($results->contains($modelALocationC));
        $this->assertFalse($results->contains($modelBLocationC));

    }

    public function testFilterAssetANDModelStatus()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();

        $modelAStatusA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $modelAStatusB = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $modelAStatusA = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);
        $modelAStatusA = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusB->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'status_label',
                'value' => [$statusB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($modelAStatusB));
        $this->assertFalse($results->contains($modelAStatusA));
        $this->assertFalse($results->contains($modelAStatusA));
        $this->assertFalse($results->contains($modelAStatusA));

    }

    public function testFilterAssetModelStatusArray()
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        $statusA = Statuslabel::factory()->create();
        $statusB = Statuslabel::factory()->create();
        $statusC = Statuslabel::factory()->create();

        $modelAStatusA = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusA->id]);
        $modelAStatusB = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusB->id]);
        $modelAStatusC = Asset::factory()->create(['model_id' => $modelA->id, 'status_id' => $statusC->id]);
        $modelBStatusA = Asset::factory()->create(['model_id' => $modelB->id, 'status_id' => $statusA->id]);

        $filter = [
            ["field"=>"model","value"=>[$modelA->name],"operator"=>"contains","logic"=>"AND"],
            ["field"=>"status_label","value"=>[$statusA->name, $statusB->name],"operator"=>"contains","logic"=>"AND"],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        Log::error($results);

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($modelAStatusA));
        $this->assertTrue($results->contains($modelAStatusB));
        $this->assertFalse($results->contains($modelAStatusC));
        $this->assertFalse($results->contains($modelBStatusA));
    }


    public function testFilterAssetModelManufacturer()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelAManufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelCManufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelCManufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelDManufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $assetModelAManufacturerA = Asset::factory()->create(['model_id' => $modelAManufacturerA->id]);
        $assetModelBManufacturerA = Asset::factory()->create(['model_id' => $modelCManufacturerA->id]);
        $assetModelCManufacturerB = Asset::factory()->create(['model_id' => $modelCManufacturerB->id]);
        $assetModelDManufacturerB = Asset::factory()->create(['model_id' => $modelDManufacturerB->id]);

        $filter = [
            ["field"=>"model",          "value"=>[$modelAManufacturerA->name], "operator"=>"contains","logic"=>"AND"],
            ["field"=>"manufacturer",   "value"=>[$manufacturerA->name],        "operator"=>"contains","logic"=>"AND"],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetModelAManufacturerA));
        $this->assertFalse($results->contains($assetModelBManufacturerA));
        $this->assertFalse($results->contains($assetModelCManufacturerB));
        $this->assertFalse($results->contains($assetModelDManufacturerB));
    }


    public function testFilterAssetModelManufacturerArray()
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelAManufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelBManufacturerA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelCManufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelDManufacturerB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        $assetModelAManufacturerA = Asset::factory()->create(['model_id' => $modelAManufacturerA->id]);
        $assetModelBManufacturerA = Asset::factory()->create(['model_id' => $modelBManufacturerA->id]);
        $assetModelCManufacturerB = Asset::factory()->create(['model_id' => $modelCManufacturerB->id]);
        $assetModelDManufacturerB = Asset::factory()->create(['model_id' => $modelDManufacturerB->id]);

        $filter = [
            [
                "field"=>"model",
                "value"=>[$modelAManufacturerA->name, $modelCManufacturerB->name],
                "operator"=>"contains",
                "logic"=>"AND"
            ],[
                "field"=>"manufacturer",
                "value"=>[$manufacturerA->name, $manufacturerB->name],
                "operator"=>"contains",
                "logic"=>"AND"
            ],
        ];
        
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetModelAManufacturerA));
        $this->assertTrue($results->contains($assetModelCManufacturerB));
        $this->assertFalse($results->contains($assetModelBManufacturerA));
        $this->assertFalse($results->contains($assetModelDManufacturerB));
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

        $filter = [
            [
                "field"=>"location",
                "value"=>[$locationA->name],
                "operator"=>"contains",
                "logic"=>"AND"
            ],[
                "field"=>"status_label",
                "value"=>[$statusB->name],
                "operator"=>"contains",
                "logic"=>"AND"
            ],
        ];

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


        $filter = [
            [
                "field"=>"location",
                "value"=>[$locationA->name, $locationB->name],
                "operator"=>"contains",
                "logic"=>"AND"
            ]
        ];
        
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

        $filter = [
            [
                'field' => 'location',
                'value' => [$locationA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

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
            [
                'field' => 'location',
                'value' => [$locationA->name, $locationB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name, $manufacturerB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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

        $filter = [
            [
                'field' => 'status_label',
                'value' => [$statusA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

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
            [
                'field' => 'status_label',
                'value' => [$statusA->name, $statusB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name, $manufacturerB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$locationB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'status_label',
                'value' => [$statusA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$locationA->name, $locationB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'status_label',
                'value' => [$statusA->name, $statusB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$locationA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$modelA->name, $modelB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$locationA->name, $locationB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name, $manufacturerB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'status_label',
                'value' => [$statusA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$modelA->name, $modelB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name, $manufacturerB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'model',
                'value' => [$model->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$location->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'manufacturer',
                'value' => [$manufacturer->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'status_label',
                'value' => [$status->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            ["field"=>"model",          "value"=>[$modelA->name, $modelB->name],"operator"=>"contains","logic"=>"AND"],
            ["field"=>"location",       "value"=>[$locationA->name, $locationB->name],"operator"=>"contains","logic"=>"AND"],
            ["field"=>"manufacturer",   "value"=>[$manufacturerA->name, $manufacturerB->name],"operator"=>"contains","logic"=>"AND"],
            ["field"=>"status_label",   "value"=>[$statusA->name, $statusB->name],"operator"=>"contains","logic"=>"AND"],

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

        $filter = [
            [
                'field' => 'model',
                'value' => [],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
    }

    public function testFilterWithNonexistentValueReturnsNone()
    {
        Asset::factory()->count(3)->create();

        $filter = [
            [
                'field' => 'status_label',
                'value' => ['NonexistentStatus'],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
    }

    public function testFilterWithMixedValuesReturnsMatchingOnly()
    {
        $modelA = AssetModel::factory()->create();
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => [$modelA->name, 'NonexistentStatus'],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterWithDuplicateValuesReturnsUniqueResults()
    {
        $modelA = AssetModel::factory()->create();
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => [$modelA->name, $modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterWithNullValueReturnsNone()
    {
        $numberOfAssets = 5;
        Asset::factory()->count($numberOfAssets)->create();

        $filter = [
            [
                "field"=>"location",
                "value"=>null,
                "operator"=>"contains",
                "logic"=>"AND"],
        ];

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

        $filter = [
            [
                'field' => 'model',
                'value' => [$modelA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ],[
                'field' => 'manufacturer',
                'value' => ['NonexistentManufaturer'],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
    }

    public function testLargeArrayOfModelsReturnsAllMatching()
    {
        $models = AssetModel::factory()->count(50)->create();
        foreach ($models as $model) {
            Asset::factory()->create(['model_id' => $model->id]);
        }

        $filter = [
            [
                'field' => 'model',
                'value' => [$models->pluck('name')->toArray()],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];        

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
            [
                'field' => 'model',
                'value' => [$modelA->name, $modelB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'location',
                'value' => [$locationA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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
            [
                'field' => 'manufacturer',
                'value' => [$manufacturerA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ], [
                'field' => 'model',
                'value' => [$modelA->name, $modelB->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
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

        $filter = [
            [
                'field' => 'model',
                'value' => ['Mödel#1'],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];    

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($asset));
    }

    public function testFilterAssetsWithMissingForeignKey()
    {
        $locationA = Location::factory()->create();
        $assetWithLocation = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetWithoutLocation = Asset::factory()->create(['location_id' => null]);

        $filter = [
            [
                'field' => 'location',
                'value' => [$locationA->name],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];  

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetWithLocation));
        $this->assertFalse($results->contains($assetWithoutLocation));
    }
}