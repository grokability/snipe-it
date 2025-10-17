<?php
namespace Tests\Unit;

use UnexpectedValueException;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;
use Tests\Support\GetExtendedPrefix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignedToQueryTest extends TestCase
{
    use GetExtendedPrefix;
    use RefreshDatabase;



    public function testFilterAssetAssignedToUserId() 
    {

        $userA = User::factory()->create(['first_name'=>'UAA']);
        $userB = User::factory()->create(['first_name'=>'UBB']);
        $assetA = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userA->id]);
        $assetB = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userB->id]);

        $filter = [
            ['field'=>'assigned_to','value'=>$userA->first_name,'operator'=>'equals','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>User::class,'operator'=>'equals','logic'=>'AND'],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserIdWithType() 
    {
        $userA = User::factory()->create(['first_name'=>'U1']);
        $userB = User::factory()->create(['first_name'=>'U2']);
        $assetA = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userA->id]);
        $assetB = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userB->id]);

        $filter = [
            ['field'=>'assigned_to','value'=>'U1','operator'=>'equals','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>User::class,'operator'=>'equals','logic'=>'AND'],
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

        $partial = self::getExtendedPrefix($userA->first_name, $userB->first_name);

        $filter  = ['assigned_to' => $partial];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNameCompleteWithoutType() 
    {
        $userA = User::factory()->create(['first_name'=>'CARA']);
        $userB = User::factory()->create(['first_name'=>'DORA']);
        $assetA = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userA->id]);
        $assetB = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userB->id]);

        $filter = [
            [
                "field" => "assigned_to",
                "value" => 'Cara',
                "operator" => "contains",
                "logic" => "AND"
            ]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNameAsStringCompleteWithType() 
    {
        $userA = User::factory()->create(['first_name'=>'CARA']);
        $userB = User::factory()->create(['first_name'=>'DORA']);
        $assetA = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userA->id]);
        $assetB = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userB->id]);

        $filter = [
            [
                "field" => "assigned_to",
                "value" => [
                    [
                        "assignedType" => User::class,
                        "assigned_to" => 'CARA'
                    ]
                ],
                "operator" => "contains",
                "logic" => "AND"
            ]
        ];

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

        $partial = self::getExtendedPrefix($userA->first_name, $userB->first_name);
        $filter  = [
            ['field'=>'assigned_to','value'=>$partial,'operator'=>'contains','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>User::class,'operator'=>'equals','logic'=>'AND'],
        ];

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

    // --- Location assignment tests ---
    public function testFilterAssetAssignedToLocationId() 
    {

        $locationA = Location::factory()->create(['name'=>'LOC-A']);
        $locationB = Location::factory()->create(['name'=>'LOC-B']);

        $assetA = Asset::factory()->create(['assigned_type'=>Location::class,'assigned_to'=>$locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type'=>Location::class,'assigned_to'=>$locationB->id]);

        $filter = [
            ['field'=>'assigned_to','value'=>'LOC-A','operator'=>'equals','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>Location::class,'operator'=>'equals','logic'=>'AND'],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationIdWithType() 
    {
        $locationA = Location::factory()->create(['name'=>'L1']);
        $locationB = Location::factory()->create(['name'=>'L2']);

        $assetA = Asset::factory()->create(['assigned_type'=>Location::class,'assigned_to'=>$locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type'=>Location::class,'assigned_to'=>$locationB->id]);

        $filter = [
            ['field'=>'assigned_to','value'=>'L1','operator'=>'equals','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>Location::class,'operator'=>'equals','logic'=>'AND'],
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

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $partial = self::getExtendedPrefix($locationA->name, $locationB->name);
        $filter  = ['assigned_to' => $partial];

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

        $filter = [
            [
                "field" => "assigned_to",
                "value" => $locationA->name,
                "operator" => "contains",
                "logic" => "AND"
            ]
        ];

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

        $partial = AssignedToQueryTest::getExtendedPrefix($locationA->name, $locationB->name);
        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    [
                        'assignedType' => Location::class,
                        'assigned_to' => $partial
                    ]
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ]
        ];

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

    // --- Asset assignment tests ---
    public function testFilterAssetAssignedToAssetIdWithType() 
    {
        $parentA = Asset::factory()->create();
        $parentB = Asset::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = [
            [
                "field" => "assigned_to",
                "value" => [
                    [
                        "assignedType" => Asset::class,
                        "assigned_to" => $parentA->id
                    ]
                ],
                "operator" => "contains",
                "logic" => "AND"
            ]
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
        $parentB = Asset::factory()->create(['name' => 'parentAssetB']);
        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $partial = AssignedToQueryTest::getExtendedPrefix($parentA->name, $parentB->name);
        $filter = ['assigned_to' => $partial];

        dump($parentA);
        dump($parentB);
        dump($partial);

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

        $filter = [
            [
                "field"=>"assigned_to",
                "value"=>$parentA->name,
                "operator"=>"contains",
                "logic"=>"AND"
            ]
        ];

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

        $partial = AssignedToQueryTest::getExtendedPrefix($parentA->name, $parentB->name);

                $filter = [
            [
                "field"=>"assigned_to",
                "value"=>$partial,
                "operator"=>"contains",
                "logic"=>"AND"
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToAssetEmptyStringReturnsAll()
    {
        $parentA = Asset::factory()->create(['name' => 'parentA']);
        $parentB = Asset::factory()->create(['name' => 'parentB']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentB->id]);

        $filter = ['assigned_to' => ''];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }


    public function testFilterAssetAssignedTo_User_only() 
    {
        $userA = User::factory()->create();
        $assetA = Asset::factory()->create(['assigned_type'=>User::class,'assigned_to'=>$userA->id]);
        $other  = Asset::factory()->count(3)->create();

        $filter = [
            ['field'=>'assigned_to','value'=>$userA->first_name,'operator'=>'equals','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>User::class,'operator'=>'equals','logic'=>'AND'],
        ];
        $res = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $res);
        $this->assertTrue($res->contains($assetA));
    }

    public function testFilterAssetAssignedTo_Location_only() 
    {
        $locationA = Location::factory()->create();
        $assetC = Asset::factory()->create(['assigned_type'=>Location::class,'assigned_to'=>$locationA->id]);
        $other  = Asset::factory()->count(3)->create();

        $filter = [
            ['field'=>'assigned_to','value'=>$locationA->name,'operator'=>'equals','logic'=>'AND'],
            ['field'=>'assigned_type','value'=>Location::class,'operator'=>'equals','logic'=>'AND'],
        ];
        $res = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $res);
        $this->assertTrue($res->contains($assetC));
    }
}