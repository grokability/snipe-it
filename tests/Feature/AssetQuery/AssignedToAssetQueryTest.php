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

class AssignedToAssetQueryTest extends TestCase
{
    use GetExtendedPrefix;
    use RefreshDatabase;

    /*
     Test empty filters
    */

    public function testFilterAssetAssignedToAssetAndEqualsEmpty()
    {
        $parentAssetA = Asset::factory()->create(['name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => ''
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(4, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }
    public function testFilterAssetAssignedToAssetNotEqualsEmpty()
    {
        $parentAssetA = Asset::factory()->create(['name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => ''
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }
    public function testFilterAssetAssignedToAssetAndContainsEmpty()
    {
        $parentAssetA = Asset::factory()->create(['name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => ''
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(4, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }
    public function testFilterAssetAssignedToAssetNotContainsEmpty()
    {
        $parentAssetA = Asset::factory()->create(['name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => ''
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(2, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

    /*
     * Equals and not equals
     */

    public function testFilterAssetAssignedToAssetNameAndEquals()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->name
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
    
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($parentAssetA));
        $this->assertFalse($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetTagAndEquals()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->asset_tag
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($parentAssetA));
        $this->assertFalse($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetNameNotEquals()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->name
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetTagNotEquals()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->asset_tag
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

    /*
     * Contains and not contains
     */

    public function testFilterAssetAssignedToAssetNameAndContainsComplete()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->name
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($parentAssetA));
        $this->assertFalse($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetNameAndContainsPartital()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => 'pc'
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($parentAssetA));
        $this->assertFalse($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetTagAndContainsComplete()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->asset_tag
                ],
                'operator' => 'equals',
                'logic' => 'contains'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($parentAssetA));
        $this->assertFalse($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetTagAndContainsPartial()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => 'pc'
                ],
                'operator' => 'contains',
                'logic' => 'contains'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($parentAssetA));
        $this->assertFalse($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetNameNotContainsComplete()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->name
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetNameNotContainsPartial()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => 'pc'
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetTagNotContainsComplete()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->asset_tag
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

    public function testFilterAssetAssignedToAssetTagNotContainsPartial()
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => 'pc'
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(3, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($parentAssetA));
        $this->assertTrue($results->contains($parentAssetB));
    }

}