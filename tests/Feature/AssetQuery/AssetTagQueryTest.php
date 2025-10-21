<?php
namespace Tests\Feature\AssetQuery;

use App\Models\Category;
use App\Models\Asset;
use App\Models\AssetModel;
use Log;
use Tests\Support\GetExtendedPrefix;
use Tests\TestCase;


class AssetTagQueryTest extends TestCase
{
    public function testFilterAssetTagAndEquals()
    {
        $assetA = Asset::factory()->create(['asset_tag' => '1']);
        $assetB = Asset::factory()->create(['asset_tag' => '2']);
        $assetC = Asset::factory()->create(['asset_tag' => '21']);
        $assetD = Asset::factory()->create(['asset_tag' => '42']);

        $filter = [
            [
                'field' => 'asset_tag',
                'value' => ['1'],
                'operator' => 'equals',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        Log::error($results);

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

        
    public function testFilterAssetTagAndContains()
    {
        $assetA = Asset::factory()->create(['asset_tag' => '1']);
        $assetB = Asset::factory()->create(['asset_tag' => '2']);
        $assetC = Asset::factory()->create(['asset_tag' => '21']);
        $assetD = Asset::factory()->create(['asset_tag' => '42']);

        $filter = [
            [
                'field' => 'asset_tag',
                'value' => ['1'],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        Log::error($results);

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterAssetTagNotEquals()
    {
        $assetA = Asset::factory()->create(['asset_tag' => '1']);
        $assetB = Asset::factory()->create(['asset_tag' => '2']);
        $assetC = Asset::factory()->create(['asset_tag' => '21']);
        $assetD = Asset::factory()->create(['asset_tag' => '42']);

        $filter = [[
            'field' => 'asset_tag',
            'value' => ['1'],
            'operator' => 'equals',
            'logic' => 'NOT',
        ]];

        $results = Asset::query()->byFilter($filter)->get();

        Log::error($results);

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
    }

    public function testFilterAssetTagNotContains()
    {
        $assetA = Asset::factory()->create(['asset_tag' => '1']);
        $assetB = Asset::factory()->create(['asset_tag' => '2']);
        $assetC = Asset::factory()->create(['asset_tag' => '21']);
        $assetD = Asset::factory()->create(['asset_tag' => '42']);

        $filter = [[
            'field' => 'asset_tag',
            'value' => ['1'],
            'operator' => 'contains',
            'logic' => 'NOT',
        ]];

        $results = Asset::query()->byFilter($filter)->get();

        Log::error($results);

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetD));
    }
}