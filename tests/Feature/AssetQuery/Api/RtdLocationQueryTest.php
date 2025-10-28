<?php

namespace Tests\Feature\AssetQuery\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class RtdLocationQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsRtdLocationEmptyString(): void
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = [
            [
                'field' => 'rtd_location',
                'value' => [''],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
        ];

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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ])
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }

    public function testFilterAssetsRtdLocationString(): void
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);

        $filter = ['rtd_location' => $locationA->name];//

        $filter = [
            [
                "field"=>"rtd_location",
                "value"=>[$locationA->name],
                "operator"=>"contains",
                "logic"=>"AND"
            ]
        ];

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
                'id' => $assetA->id,
            ]);
    }

    public function testFilterAssetsRtdLocationArray(): void
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();

        $assetA = Asset::factory()->create(['rtd_location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['rtd_location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['rtd_location_id' => $locationC->id]);

        $filter = [
            [
                'field' => 'rtd_location',
                'value' => [$locationA->id, $locationC->id],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
        ];

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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ]);
    }
}
