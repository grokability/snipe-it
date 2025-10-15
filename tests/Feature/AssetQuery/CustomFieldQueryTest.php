<?php
namespace Tests\Unit;

use App\Models\Asset;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomFieldQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'custom_text')) {
                $table->string('custom_text')->nullable()->index();
            }
            if (!Schema::hasColumn('assets', 'custom_flag')) {
                $table->string('custom_flag')->nullable()->index(); 
            }
            if (!Schema::hasColumn('assets', 'custom_code')) {
                $table->string('custom_code')->nullable()->index();
            }
        });
    }

    public function testFilterBySingleCustomFieldStringLike()
    {
        $aMatch    = Asset::factory()->create(['custom_text' => 'Alpha Blue']);
        $aNoMatch1 = Asset::factory()->create(['custom_text' => 'Gamma Green']);
        $aNoMatch2 = Asset::factory()->create(['custom_text' => 'Delta Red']);

        $filter = [
            [
                'field' => 'custom_text',
                'value' => 'Blu',
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($aMatch));
        $this->assertFalse($results->contains($aNoMatch1));
        $this->assertFalse($results->contains($aNoMatch2));
    }

    public function testFilterBooleanLikeCustomFieldArrayAndString()
    {
        $on  = Asset::factory()->create(['custom_flag' => '1']);
        $off = Asset::factory()->create(['custom_flag' => '0']);

        $filterOn = [
            [
                'field' => 'custom_flag',
                'value' => '1',
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $filterOff = [
            [
                'field' => 'custom_flag',
                'value' => '1',
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $resIn = Asset::query()->byFilter($filterOn)->get();
        $this->assertCount(1, $resIn);
        $this->assertTrue($resIn->contains($on));
        $this->assertFalse($resIn->contains($off));

        $resLike = Asset::query()->byFilter($filterOff)->get();
        $this->assertCount(1, $resLike);
        $this->assertTrue($resLike->contains($on));
        $this->assertFalse($resLike->contains($off));
    }

    public function testFilterMultipleCustomFieldsCombined()
    {

        $hit      = Asset::factory()->create(['custom_text' => 'Report Q3', 'custom_code' => 'R-2025']);
        $missText = Asset::factory()->create(['custom_text' => 'Notes Q3',  'custom_code' => 'R-2025']);
        $missCode = Asset::factory()->create(['custom_text' => 'Report Q3', 'custom_code' => 'X-0001']);

        $filter = [
            [
                'field' => 'custom_text',
                'value' => 'Report',
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'custom_code',
                'value' => 'R-2025',
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($hit));
        $this->assertFalse($results->contains($missText));
        $this->assertFalse($results->contains($missCode));
    }

    public function testFilterWithEmptyArrayLeavesResultsUnchanged()
    {
        $a = Asset::factory()->create(['custom_text' => 'A']);
        $b = Asset::factory()->create(['custom_text' => 'B']);

        $filter = [[]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($a));
        $this->assertTrue($results->contains($b));
    }

    public function testFilterWithNonexistentValueReturnsNone()
    {
        Asset::factory()->count(3)->create(['custom_text' => 'X']);
        $filter = [
            [
                'field' => 'custom_text',
                'value' => 'does-not-exist',
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(0, $results);
    }

    public function testFilterWithSpecialCharactersInCustomField()
    {
        $match = Asset::factory()->create(['custom_text' => 'Mödel#1 (ß)']);
        $nope  = Asset::factory()->create(['custom_text' => 'Model 2']);

        $filter = [
            [
                'field' => 'custom_text',
                'value' => 'Mödel#1',
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($match));
        $this->assertFalse($results->contains($nope));
    }
}
