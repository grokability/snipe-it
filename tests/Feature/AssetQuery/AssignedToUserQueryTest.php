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

class AssignedToUserQueryTest extends TestCase
{
    use GetExtendedPrefix;
    use RefreshDatabase;

    /*
     Test empty filters
    */

    public function testFilterAssetAssignedToUserAndEqualsEmpty()
    {
        $userA = User::factory()->create(['first_name' => 'Snaggrit', 'last_name' => 'Filthsnout']);
        $userB = User::factory()->create(['first_name' => 'Klikpik', 'last_name' => 'Rustfingers']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
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
    public function testFilterAssetAssignedToUserNotEqualsEmpty()
    {
        $userA = User::factory()->create(['first_name' => 'Grubnash', 'last_name' => 'Wormchewer']);
        $userB = User::factory()->create(['first_name' => 'Vriggle', 'last_name' => 'Mudspine']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
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
    public function testFilterAssetAssignedToUserAndContainsEmpty()
    {
        $userA = User::factory()->create(['first_name' => 'Grubnash', 'last_name' => 'Wormchewer']);
        $userB = User::factory()->create(['first_name' => 'Vriggle', 'last_name' => 'Mudspine']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
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
    public function testFilterAssetAssignedToAssetNotContainsEmpty()
    {
        $userA = User::factory()->create(['first_name' => 'Mucksnip', 'last_name' => 'Rotfoot']);
        $userB = User::factory()->create(['first_name' => 'Dregzit', 'last_name' => 'Spleenbiter']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
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
    public function testFilterAssetAssignedToUserAndEqualsFirstName()
    {
        $userA = User::factory()->create(['first_name' => 'Snortblix', 'last_name' => 'Ashclatter']);
        $userB = User::factory()->create(['first_name' => 'Kribba', 'last_name' => 'Scrapstitch']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userA->first_name,
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

    public function testFilterAssetAssignedToUserAndEqualsLastName()
    {
        $userA = User::factory()->create(['first_name' => 'Zogmuk', 'last_name' => 'Gutflinger']);
        $userB = User::factory()->create(['first_name' => 'Trigglewort', 'last_name' => 'Nailgnaw']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->last_name,
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserAndEqualsCompleteName()
    {
        $userA = User::factory()->create(['first_name' => 'Gritznab', 'last_name' => 'Smudgeclaw']);
        $userB = User::factory()->create(['first_name' => 'Pibbsnark', 'last_name' => 'Ratpinch']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->first_name . ' ' . $userB->last_name,
                ],
                'operator' => 'equals',
                'logic' => 'AND'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertFalse($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNotEqualsFirstName()
    {
        $userA = User::factory()->create(['first_name' => 'Snortblix', 'last_name' => 'Ashclatter']);
        $userB = User::factory()->create(['first_name' => 'Kribba', 'last_name' => 'Scrapstitch']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userA->first_name,
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

    public function testFilterAssetAssignedToUserNotEqualsLastName()
    {
        $userA = User::factory()->create(['first_name' => 'Zogmuk', 'last_name' => 'Gutflinger']);
        $userB = User::factory()->create(['first_name' => 'Trigglewort', 'last_name' => 'Nailgnaw']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->last_name,
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetAssignedToUserNotEqualsCompleteName()
    {
        $userA = User::factory()->create(['first_name' => 'Gritznab', 'last_name' => 'Smudgeclaw']);
        $userB = User::factory()->create(['first_name' => 'Pibbsnark', 'last_name' => 'Ratpinch']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->first_name . ' ' . $userB->last_name,
                ],
                'operator' => 'equals',
                'logic' => 'NOT'
            ],
        ];
        $results = Asset::query()->byFilter($filter)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    /*
     * Contains and not contains
     */
    public function testFilterAssetAssignedToUserAndContainsFirstNameComplete()
    {
        $userA = User::factory()->create(['first_name' => 'Rattlegrub', 'last_name' => 'Twigsneer']);
        $userB = User::factory()->create(['first_name' => 'Skivvix', 'last_name' => 'Bleakgrin']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userA->first_name,
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

    public function testFilterAssetAssignedToUserAndContainsFirstNamePartial()
    {
        $userA = User::factory()->create(['first_name' => 'Rattlegrub', 'last_name' => 'Twigsneer']);
        $userB = User::factory()->create(['first_name' => 'Skivvix', 'last_name' => 'Bleakgrin']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => 'grub',
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

    public function testFilterAssetAssignedToUserAndContainsLastNameComplete()
    {
        $userA = User::factory()->create(['first_name' => 'Hobznok', 'last_name' => 'Nailspitter']);
        $userB = User::factory()->create(['first_name' => 'Nibblit', 'last_name' => 'Grimepocket']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->last_name,
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

    public function testFilterAssetAssignedToUserAndContainsLastNamePartial()
    {
        $userA = User::factory()->create(['first_name' => 'Hobznok', 'last_name' => 'Nailspitter']);
        $userB = User::factory()->create(['first_name' => 'Nibblit', 'last_name' => 'Grimepocket']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => 'pocket',
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

    public function testFilterAssetAssignedToUserAndContainsCompleteNameComplete()
    {
        $userA = User::factory()->create(['first_name' => 'Gorpzack', 'last_name' => 'Sootsnort']);
        $userB = User::factory()->create(['first_name' => 'Skratcha', 'last_name' => 'Funguspike']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->first_name . ' ' . $userB->last_name,
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

    public function testFilterAssetAssignedToUserAndContainsCompleteNamePartial()
    {
        $userA = User::factory()->create(['first_name' => 'Gorpzack', 'last_name' => 'Sootsnort']);
        $userB = User::factory()->create(['first_name' => 'Skratcha', 'last_name' => 'Funguspike']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => 'cha fun',
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

    public function testFilterAssetAssignedToUserNotContainsFirstNameComplete()
    {
        $userA = User::factory()->create(['first_name' => 'Fizzgrub', 'last_name' => 'Sproingjaw']);
        $userB = User::factory()->create(['first_name' => 'Blortwig', 'last_name' => 'Shankspark']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userA->first_name,
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

    public function testFilterAssetAssignedToUserNotContainsFirstNamePartial()
    {
        $userA = User::factory()->create(['first_name' => 'Fizzgrub', 'last_name' => 'Sproingjaw']);
        $userB = User::factory()->create(['first_name' => 'Blortwig', 'last_name' => 'Shankspark']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => 'fizz',
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

    public function testFilterAssetAssignedToUserNotContainsLastNameComplete()
    {
        $userA = User::factory()->create(['first_name' => 'Krakstik', 'last_name' => 'Filchmask']);
        $userB = User::factory()->create(['first_name' => 'Splugwort', 'last_name' => 'Mosscackle']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->last_name,
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

    public function testFilterAssetAssignedToUserNotContainsLastNamePartial()
    {
        $userA = User::factory()->create(['first_name' => 'Krakstik', 'last_name' => 'Filchmask']);
        $userB = User::factory()->create(['first_name' => 'Splugwort', 'last_name' => 'Mosscackle']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => 'kle',
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

    public function testFilterAssetAssignedToUserNotContainsCompleteNameComplete()
    {
        $userA = User::factory()->create(['first_name' => 'Vibblesnap', 'last_name' => 'Tangletoe']);
        $userB = User::factory()->create(['first_name' => 'Grobnix', 'last_name' => 'Smeltwhisk']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->first_name . ' ' . $userB->last_name,
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

    public function testFilterAssetAssignedToUserNotContainsCompleteNamePartial()
    {
        $userA = User::factory()->create(['first_name' => 'Vibblesnap', 'last_name' => 'Tangletoe']);
        $userB = User::factory()->create(['first_name' => 'Grobnix', 'last_name' => 'Smeltwhisk']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => 'nix sme',
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

}