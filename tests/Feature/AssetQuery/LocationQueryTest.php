<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Location;
use Tests\TestCase;


class LocationQueryTest extends TestCase
{
    public function testFilterAssetLocationEmptyString()
    {

        // Given: Location and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);

        $filter = ['location' => ''];
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

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);

        $filter = ['location' => $locationA->name];
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

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);

        $queryString = substr($locationA->name, 0, floor(strlen($locationA->name) / 2));
        $filter = ['location' => $queryString];
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

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);

        $filter = ['location' => [$locationA->name]];
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

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['location_id' => $locationC->id]);
        $assetD = Asset::factory()->create(['location_id' => $locationD->id]);
        $assetE = Asset::factory()->create(['location_id' => $locationE->id]);

        // When: Query with an array of names
        $filter = ['location' => [$locationB->name, $locationE->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }

    public function testFilterAssetLoationId()
    {

        // Given: Locations and assets
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);

        $filter = ['location' => $locationA->id];
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

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);

        $filter = ['location' => [$locationA->id]];
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

        $assetA = Asset::factory()->create(['location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['location_id' => $locationC->id]);

        $filter = ['location' => [$locationA->id, $locationB->name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA and assetB
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));

    }

}