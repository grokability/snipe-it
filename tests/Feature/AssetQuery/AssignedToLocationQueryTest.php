<?php
namespace Tests\Feature\AssetQuery;

use UnexpectedValueException;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;
use Tests\Support\GetExtendedPrefix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignedToLocationQueryTest extends TestCase
{
    use GetExtendedPrefix;
    use RefreshDatabase;

    /*
     Test empty filters
    */

    public function testFilterAssetAssignedToLocationAndEqualsEmpty()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => ''
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }
    public function testFilterAssetAssignedToLocationNotEqualsEmpty()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => ''
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }
    public function testFilterAssetAssignedToLocationAndContainsEmpty()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => ''
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }
    public function testFilterAssetAssignedToLocationNotContainsEmpty()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => ''
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(0, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    /*
     * Equals and not equals
    */

    public function testFilterAssetAssignedToLocationAndEquals()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => $locationA->name
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNotEquals()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => $locationA->name
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    /*
     * Contains and not contains
    */

    public function testFilterAssetAssignedToLocationAndContainsPartial()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => 'Hel'
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationAndContainsComplete()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => $locationA->name
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNotContainsPartial()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => 'Hel'
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToLocationNotContainsComplete()
    {
        $locationA = Location::factory()->create(['name' => 'Oslo']);
        $locationB = Location::factory()->create(['name' => 'Helsinki']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => $locationA->name
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

}