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

        $filter  = ['custom_fields.custom_text' => 'Blu']; 
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($aMatch));
        $this->assertFalse($results->contains($aNoMatch1));
        $this->assertFalse($results->contains($aNoMatch2));
    }

    public function testFilterBySingleCustomFieldArrayWhereIn()
    {
        $a1 = Asset::factory()->create(['custom_text' => 'ValueA']);
        $a2 = Asset::factory()->create(['custom_text' => 'ValueB']);
        $a3 = Asset::factory()->create(['custom_text' => 'ValueC']);

        $filter  = ['custom_fields.custom_text' => ['ValueA', 'ValueB']];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($a1));
        $this->assertTrue($results->contains($a2));
        $this->assertFalse($results->contains($a3));
    }

    public function testFilterBooleanLikeCustomFieldArrayAndString()
    {
        $on  = Asset::factory()->create(['custom_flag' => '1']);
        $off = Asset::factory()->create(['custom_flag' => '0']);

        $resIn = Asset::query()->byFilter(['custom_fields.custom_flag' => ['1']])->get();
        $this->assertCount(1, $resIn);
        $this->assertTrue($resIn->contains($on));
        $this->assertFalse($resIn->contains($off));

        $resLike = Asset::query()->byFilter(['custom_fields.custom_flag' => '1'])->get();
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
            'custom_fields.custom_text' => 'Report',
            'custom_fields.custom_code' => 'R-2025',  
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

        $filter  = ['custom_fields.custom_text' => []];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(0, $results);
        $this->assertFalse($results->contains($a));
        $this->assertFalse($results->contains($b));
    }

    public function testFilterWithNonexistentValueReturnsNone()
    {
        Asset::factory()->count(3)->create(['custom_text' => 'X']);
        $filter  = ['custom_fields.custom_text' => 'does-not-exist'];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(0, $results);
    }

    public function testFilterCombinationArrayAndStringAcrossCustomFields()
    {
        $keep1 = Asset::factory()->create(['custom_text' => 'Alpha', 'custom_code' => 'G-100']);
        $keep2 = Asset::factory()->create(['custom_text' => 'Beta',  'custom_code' => 'G-100']);
        $drop  = Asset::factory()->create(['custom_text' => 'Gamma', 'custom_code' => 'Z-999']);

        $filter = [
            'custom_fields.custom_text' => ['Alpha', 'Beta'], 
            'custom_fields.custom_code' => 'G-100',         
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($keep1));
        $this->assertTrue($results->contains($keep2));
        $this->assertFalse($results->contains($drop));
    }

    public function testFilterWithSpecialCharactersInCustomField()
    {
        $match = Asset::factory()->create(['custom_text' => 'Mödel#1 (ß)']);
        $nope  = Asset::factory()->create(['custom_text' => 'Model 2']);

        $filter  = ['custom_fields.custom_text' => 'Mödel#1'];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($match));
        $this->assertFalse($results->contains($nope));
    }
}
