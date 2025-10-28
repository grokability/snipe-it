<?php

namespace Tests\Feature\AssetQuery;

use Tests\TestCase;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\GetExtendedPrefix;


class QueryLogicTest extends TestCase
{
    use RefreshDatabase;
    use GetExtendedPrefix;

    public function testModelContainsAndManufacturerContains()
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $dell = Manufacturer::factory()->create(['name' => 'Dell']);

        $macbook = AssetModel::factory()->create(['name' => 'MacBook Pro', 'manufacturer_id' => $apple->id]);
        $xps = AssetModel::factory()->create(['name' => 'XPS 15', 'manufacturer_id' => $dell->id]);

        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetDell = Asset::factory()->create(['model_id' => $xps->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => ['macbook'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetMacbook));
        $this->assertFalse($results->contains($assetDell));
    }

    public function testModelContainsAndManufacturerNotContains()
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $dell = Manufacturer::factory()->create(['name' => 'Dell']);

        $macbook = AssetModel::factory()->create(['name' => 'MacBook Pro', 'manufacturer_id' => $apple->id]);
        $xps = AssetModel::factory()->create(['name' => 'XPS 15', 'manufacturer_id' => $dell->id]);

        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetDell = Asset::factory()->create(['model_id' => $xps->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => ['macbook'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'NOT',
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(0, $results);
    }

    public function testPartialModelMatchAndExactManufacturer()
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $dell = Manufacturer::factory()->create(['name' => 'Dell']);

        $macbook = AssetModel::factory()->create(['name' => 'MacBook Pro', 'manufacturer_id' => $apple->id]);
        $xps = AssetModel::factory()->create(['name' => 'XPS 15', 'manufacturer_id' => $dell->id]);

        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetDell = Asset::factory()->create(['model_id' => $xps->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => ['macb'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetMacbook));
        $this->assertFalse($results->contains($assetDell));
    }

    public function testPartialModelMatchAndManufacturerNotMatch()
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $dell = Manufacturer::factory()->create(['name' => 'Dell']);

        $macbook = AssetModel::factory()->create(['name' => 'MacBook Pro', 'manufacturer_id' => $apple->id]);
        $xps = AssetModel::factory()->create(['name' => 'XPS 15', 'manufacturer_id' => $dell->id]);

        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetDell = Asset::factory()->create(['model_id' => $xps->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => ['macb'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'NOT',
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(0, $results);
    }

    public function testModelContainsBookButExcludeAppleManufacturer()
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $microsoft = Manufacturer::factory()->create(['name' => 'Microsoft']);
        $asus = Manufacturer::factory()->create(['name' => 'Asus']);

        $macbook = AssetModel::factory()->create(['name' => 'MacBook', 'manufacturer_id' => $apple->id]);
        $surfacebook = AssetModel::factory()->create(['name' => 'SurfaceBook', 'manufacturer_id' => $microsoft->id]);
        $zenbook = AssetModel::factory()->create(['name' => 'ZenBook', 'manufacturer_id' => $asus->id]);

        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetSurfacebook = Asset::factory()->create(['model_id' => $surfacebook->id]);
        $assetZenbook = Asset::factory()->create(['model_id' => $zenbook->id]);

        $filter = [
            [
                'field' => 'model',
                'value' => ['book'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'NOT',
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertTrue($results->contains($assetSurfacebook));
        $this->assertTrue($results->contains($assetZenbook));
        $this->assertFalse($results->contains($assetMacbook));
    }
}
