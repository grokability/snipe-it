<?php
namespace Tests\Feature\AssetQuery;

use App\Models\Asset;
use App\Models\Company;
use Tests\TestCase;


class LegacyFilterTest extends TestCase
{
    public function testFilterAssetsByCompanyId()
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);

        $filter = [
            'company_id' => $companyA->id
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterAssetsByAssetTag()
    {
        $assetA = Asset::factory()->create(['asset_tag' => 'A1']);
        $assetB = Asset::factory()->create(['asset_tag' => 'B1']);

        $filter = [
            'asset_tag' => 'A1'
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testFilterAssetsByCompanyAndAssetTag()
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $assetA = Asset::factory()->create([
            'company_id' => $companyA->id,
            'asset_tag' => 'X1'
        ]);

        $assetB = Asset::factory()->create([
            'company_id' => $companyA->id,
            'asset_tag' => 'Y1'
        ]);

        $assetC = Asset::factory()->create([
            'company_id' => $companyB->id,
            'asset_tag' => 'X1'
        ]);

        $filter = [
            'company_id' => $companyA->id,
            'asset_tag' => 'X1'
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
    }

    public function testReturnsAllAssetsWhenFilterIsEmpty()
    {
        $assetA = Asset::factory()->create();
        $assetB = Asset::factory()->create();

        $filter = [];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }
}
