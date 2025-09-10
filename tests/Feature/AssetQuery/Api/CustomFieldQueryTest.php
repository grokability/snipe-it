<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomFieldQueryTest extends TestCase
{
    use RefreshDatabase;

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


    public function testFilterBySingleCustomFieldStringLike(): void
    {
        $aMatch = Asset::factory()->create(['custom_text' => 'Here is another one']);
        $aNoMatch1 = Asset::factory()->create(['custom_text' => 'Strings are awsome']);
        $aNoMatch2 = Asset::factory()->create(['custom_text' => 'I am just a string']);

        $filter = ['custom_fields.custom_text' => 'is another'];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($aMatch));
        $this->assertFalse($results->contains($aNoMatch1));
        $this->assertFalse($results->contains($aNoMatch2));


        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.index', [
                    'status' => '',
                    'order_number' => '',
                    'company_id' => '',
                    'status_id' => '',
                    'filter' => json_encode($filter),
                    'search' => '',
                    'sort' => 'id',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '50',
                ])
            )
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $aMatch->id,
            ]);
    }

    public function testFilterMultipleCustomFieldsCombined(): void
    {
        $hit = Asset::factory()->create(['custom_text' => 'Report Q3', 'custom_code' => 'R-2025']);
        $missText = Asset::factory()->create(['custom_text' => 'Notes Q3', 'custom_code' => 'R-2025']);
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

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.index', [
                    'status' => '',
                    'order_number' => '',
                    'company_id' => '',
                    'status_id' => '',
                    'filter' => json_encode($filter),
                    'search' => '',
                    'sort' => 'id',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '50',
                ])
            )
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $hit->id,
            ]);
    }

    public function testFilterWithSpecialCharactersInCustomField(): void
    {

        $match = Asset::factory()->create(['custom_text' => 'Mödël#1 (ß)']);
        $nope = Asset::factory()->create(['custom_text' => 'ÄäÖöÜüëÅ']);

        $filter = ['custom_fields.custom_text' => 'Mödël#1'];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($match));
        $this->assertFalse($results->contains($nope));

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.index', [
                    'status' => '',
                    'order_number' => '',
                    'company_id' => '',
                    'status_id' => '',
                    'filter' => json_encode($filter),
                    'search' => '',
                    'sort' => 'id',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '50',
                ])
            )
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $match->id,
            ]);
    }

    public function testFilterWithUTF8CharactersInCustomField(): void
    {

        $match = Asset::factory()->create(['custom_text' => '🥶🎃😅']);
        $nope = Asset::factory()->create(['custom_text' => '🙃🥳🙄😵‍💫']);

        $filter = ['custom_fields.custom_text' => '🎃'];
        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($match));
        $this->assertFalse($results->contains($nope));

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.index', [
                    'status' => '',
                    'order_number' => '',
                    'company_id' => '',
                    'status_id' => '',
                    'filter' => json_encode($filter),
                    'search' => '',
                    'sort' => 'id',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '50',
                ])
            )
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $match->id,
            ]);
    }
}