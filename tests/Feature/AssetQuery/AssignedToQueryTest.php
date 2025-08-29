<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;


class AssignedToQueryTest extends TestCase
{

    // Assigned to user
    public function testFilterAssetAssignedToUserFirstnameEmptyString()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        //dd($results);

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserFirstnameStringComplete()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->first_name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserFirstnameStringPartial()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $queryString = substr($userA->first_name, 0, floor(strlen($userA->first_name) / 2));
        $filter = ['assigned_to' => $queryString];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserFirstnameArraySingle()
    {

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => [$userA->first_name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetAssignedToUserFirstnameArrayMultiple()
    {

        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();
        $userD = User::factory()->create();
        $userE = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userC->id]);
        $assetD = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userD->id]);
        $assetE = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userE->id]);


        // When: Query with an array of names
        $filter = ['assigned_to' => [$userB->first_name, $userE->first_name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }

    public function testFilterAssetAssignedToUserLastnameEmptyString()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        //dd($results);

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserLastnameStringComplete()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->last_name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserLastnameStringPartial()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $queryString = substr($userA->last_name, 0, floor(strlen($userA->last_name) / 2));
        $filter = ['assigned_to' => $queryString];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserLastnameArraySingle()
    {

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => [$userA->last_name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));

    }

    public function testFilterAssetAssignedToUserLastnameArrayMultiple()
    {

        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();
        $userD = User::factory()->create();
        $userE = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userC->id]);
        $assetD = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userD->id]);
        $assetE = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userE->id]);


        // When: Query with an array of names
        $filter = ['assigned_to' => [$userB->last_name, $userE->last_name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetE));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));

    }
    public function testFilterAssetAssignedToUserFirstnameLastnameArrayMultiple()
    {

        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        $userA_assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $userA_assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $userB_assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $userB_assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $userB_assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $userC_assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userC->id]);


        // When: Query with an array of names
        $filter = ['assigned_to' => [$userA->first_name, $userB->last_name]];
        $results = Asset::query()->byFilter($filter)->get();

        // Then: Should include only assetA to assetD
        $this->assertCount(5, $results);
        $this->assertTrue($results->contains($userA_assetA));
        $this->assertTrue($results->contains($userA_assetB));
        $this->assertTrue($results->contains($userB_assetA));
        $this->assertTrue($results->contains($userB_assetB));
        $this->assertTrue($results->contains($userB_assetC));
        $this->assertFalse($results->contains($userC_assetA));

    }

    public function testFilterAssetAssignedToUserId()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->id];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserIdArraySingle()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => [$userA->id]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserIdAndNameArray()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();
        $userD = User::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userC->id]);
        $assetD = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userD->id]);

        $filter = ['assigned_to' => [$userA->id, $userB->first_name, $userC->last_name]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertFalse($results->contains($assetD));
    }

}