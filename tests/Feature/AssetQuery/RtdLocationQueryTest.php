<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Location;
use Tests\TestCase;


class RtdLocationQueryTest extends TestCase
{
    public function testFilterAssetLocationEmptyString()
    {

        // Given: Location and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = ['rtd_location' => ''];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));

    }


    public function testFilterAssetLocationStringComplete()
    {

        // Given: Location and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = ['rtd_location' => $locationA->name];
        $results = Asset::query()->byFilter($filter)->get();


        // Then: Should include only assetA and assetB
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetLocationStringPartial()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $queryString = substr($locationA->name, 0, floor(strlen($locationA->name) / 2));
        $filter = ['rtd_location' => $queryString];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetLocationArraySingle()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = ['rtd_location' => [$locationA->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetLocationArrayMultiple()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();
        $locationD = Location::factory()->create();
        $locationE = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['rtd_location_id' => $locationC->id]);
        $assetD = Asset::factory()->create(['rtd_location_id' => $locationD->id]);
        $assetE = Asset::factory()->create(['rtd_location_id' => $locationE->id]);

        // When: Query with an array of names
        $filter = ['rtd_location' => [$locationB->name, $locationE->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }

    public function testFilterAssetLocationId()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = ['rtd_location' => $locationA->id];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetLocationIdArraySingle()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = ['rtd_location' => [$locationA->id]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetLocationIdAndNameArray()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['rtd_location_id' => $locationC->id]);

        $filter = ['rtd_location' => [$locationA->id, $locationB->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));

    }

}