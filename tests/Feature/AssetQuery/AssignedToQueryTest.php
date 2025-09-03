<?php
namespace Tests\Unit;

use UnexpectedValueException;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class AssignedToQueryTest extends TestCase
{
    // --- User assignment tests ---
    public function testFilterAssetAssignedToUserId()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->id];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserIdWithType()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->id, 'assigned_type' => User::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserIdArray()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userC->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $userA->id, 'assignedType' => User::class],
                ['assigned_to' => $userC->id, 'assignedType' => User::class],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetC));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserIdArrayWithoutType()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $userA->id],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNameComplete()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->first_name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNamePartial()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $partial = substr($userA->first_name, 0, max(1, floor(strlen($userA->first_name) / 2)));
        $filter = ['assigned_to' => $partial];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNameCompleteWithType()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => $userA->first_name, 'assigned_type' => User::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNamePartialWithType()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $partial = substr($userA->first_name, 0, max(1, floor(strlen($userA->first_name) / 2)));
        $filter = ['assigned_to' => $partial, 'assigned_type' => User::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserEmptyStringReturnsAll()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = ['assigned_to' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));

    }

    public function testFilterAssetAssignedToUserEmptyStringWithTypeReturnsAllOfType()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $location = Location::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $location->id]);

        $filter = ['assigned_to' => '', 'assigned_type' => User::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    // --- Location assignment tests ---

    public function testFilterAssetAssignedToLocationId()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = ['assigned_to' => $locationA->id];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationIdWithType()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = ['assigned_to' => $locationA->id, 'assigned_type' => Location::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationIdArray()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationC->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $locationA->id, 'assignedType' => Location::class],
                ['assigned_to' => $locationC->id, 'assignedType' => Location::class],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetC));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationIdArrayWithoutType()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $locationA->id],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNameComplete()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = ['assigned_to' => $locationA->name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNamePartial()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $partial = substr($locationA->name, 0, max(1, floor(strlen($locationA->name) / 2)));
        $filter = ['assigned_to' => $partial];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNameCompleteWithType()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = ['assigned_to' => $locationA->name, 'assigned_type' => Location::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNamePartialWithType()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $partial = substr($locationA->name, 0, max(1, floor(strlen($locationA->name) / 2)));
        $filter = ['assigned_to' => $partial, 'assigned_type' => Location::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationEmptyStringReturnsAll()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = ['assigned_to' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationEmptyStringWithTypeReturnsAllOfType()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $user = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $user->id]);

        $filter = ['assigned_to' => '', 'assigned_type' => Location::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    // --- Asset assignment tests ---

    public function testFilterAssetAssignedToAssetId()
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = ['assigned_to' => $parentA->id];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetIdWithType()
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = ['assigned_to' => $parentA->id, 'assigned_type' => Asset::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetIdArray()
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();
        $parentC = Asset::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentC->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $parentA->id, 'assignedType' => Asset::class],
                ['assigned_to' => $parentB->id, 'assignedType' => Asset::class],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    public function testFilterAssetAssignedToAssetIdArrayWithoutType()
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $parentA->id],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetNameComplete()
    {
        $parentA = Asset::factory()->create(['name' => 'assetParentA']);
        $parentB = Asset::factory()->create(['name' => 'assetParentB']);
        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = ['assigned_to' => $parentA->name];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetNamePartial()
    {
        $parentA = Asset::factory()->create(['name' => 'assetParentA']);
        $parentB = Asset::factory()->create(['name' => 'assetParentB']);
        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $partial = substr($parentA->name, 0, max(1, floor(strlen($parentA->name) / 2)));
        $filter = ['assigned_to' => $partial];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetNameCompleteWithType()
    {
        $parentA = Asset::factory()->create(['name' => 'assetParentA']);
        $parentB = Asset::factory()->create(['name' => 'assetParentB']);
        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = ['assigned_to' => $parentA->name, 'assigned_type' => Asset::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetNamePartialWithType()
    {
        $parentA = Asset::factory()->create(['name' => 'assetParentA']);
        $parentB = Asset::factory()->create(['name' => 'parentAssetB']);
        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $partial = substr($parentA->name, 0, max(1, floor(strlen($parentA->name) / 2)));
        $filter = ['assigned_to' => $partial, 'assigned_type' => Asset::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetEmptyStringReturnsAll()
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = ['assigned_to' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetEmptyStringWithTypeReturnsAllOfType()
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();
        $user = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $user->id]);

        $filter = ['assigned_to' => '', 'assigned_type' => Asset::class];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    // --- Edge and mixed cases ---

    public function testFilterAssetAssignedToArrayMixedTypes()
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();
        $asset = Asset::factory()->create();

        $assetUser = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $user->id]);
        $assetLoc = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $location->id]);
        $assetAsset = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $asset->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $user->id, 'assignedType' => User::class],
                ['assigned_to' => $location->id, 'assignedType' => Location::class],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetUser));
        $this->assertTrue($results->contains($assetLoc));
        $this->assertFalse($results->contains($assetAsset));
    }

    public function testFilterAssetAssignedToArrayWithInvalidId()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("You've provided an invalid type");

        $user = User::factory()->create();
        $assetUser = Asset::factory()->create([
            'assigned_type' => User::class,
            'assigned_to' => $user->id,
        ]);

        $invalidId = 999999;

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $invalidId, 'assignedType' => User::class],
            ],
        ];

        // This should now throw the exception
        Asset::query()->byFilter($filter)->get();
    }


    public function testFilterAssetAssignedToStringUnsupportedForArray()
    {
        $user = User::factory()->create();
        $assetUser = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $user->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $user->first_name, 'assignedType' => User::class],
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
    }

    public function testFilterAssetAssignedToTypeWithArrayOverrides()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $userA->id, 'assignedType' => User::class],
            ],
            'assigned_type' => Location::class, // Should be ignored for array filter
        ];

        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }
}