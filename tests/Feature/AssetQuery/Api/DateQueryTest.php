<?php

namespace Tests\Feature\AssetQuery\Api\DateQuery;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testPurchaseDateQueryStart()
    {
        Carbon::setTestNow(Carbon::create(2023, 4, 16));

        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addDays(14)->toDateString()]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(14)->toDateString()]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addMonths(14)->toDateString()]);

        $filter = [[
            'field' => 'purchase_date',
            'value' => ['startDate' => Carbon::now()->addMonths(3)->toDateString()],
            'operator' => 'contains',
            'logic' => 'AND',
        ]];

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['filter' => json_encode($filter)]))
            ->assertOk()
            ->assertJsonStructure(['total', 'rows'])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment(['id' => $assetB->id])
            ->assertJsonFragment(['id' => $assetC->id]);
    }

    public function testPurchaseDateQueryRange()
    {
        Carbon::setTestNow(Carbon::create(2011, 5, 15));

        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(50)->toDateString()]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(75)->toDateString()]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(100)->toDateString()]);
        $assetD = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(125)->toDateString()]);
        $assetE = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(150)->toDateString()]);

        $filter = [[
            'field' => 'purchase_date',
            'value' => [
                'startDate' => Carbon::now()->addWeeks(70)->toDateString(),
                'endDate'   => Carbon::now()->addWeeks(130)->toDateString(),
            ],
            'operator' => 'contains',
            'logic' => 'AND',
        ]];

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['filter' => json_encode($filter)]))
            ->assertOk()
            ->assertJsonStructure(['total', 'rows'])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc())
            ->assertJsonFragment(['id' => $assetB->id])
            ->assertJsonFragment(['id' => $assetC->id])
            ->assertJsonFragment(['id' => $assetD->id]);
    }

    public function testEolDateQueryEnd()
    {
        Carbon::setTestNow(Carbon::create(2020,1,1));

        $owner = User::factory()->superuser()->create();

        $modelA = AssetModel::factory()->create(['eol' => 12]);
        $modelB = AssetModel::factory()->create(['eol' => 24]);
        $modelC = AssetModel::factory()->create(['eol' => 36]);

        $purchase = '2020-01-01';
        $eolA = '2021-01-01';
        $eolB = '2022-01-01';
        $eolC = '2023-01-01';

        $assetA = Asset::factory()->create([
            'model_id'       => $modelA->id,
            'purchase_date'  => $purchase,
            'asset_eol_date' => $eolA,
            'asset_tag'      => 'API-EOLEND-A',
            'created_by'     => $owner->id,
        ]);
        $assetB = Asset::factory()->create([
            'model_id'       => $modelB->id,
            'purchase_date'  => $purchase,
            'asset_eol_date' => $eolB,
            'asset_tag'      => 'API-EOLEND-B',
            'created_by'     => $owner->id,
        ]);
        $assetC = Asset::factory()->create([
            'model_id'       => $modelC->id,
            'purchase_date'  => $purchase,
            'asset_eol_date' => $eolC,
            'asset_tag'      => 'API-EOLEND-C',
            'created_by'     => $owner->id,
        ]);

        $filter = [
            [
                'field'    => 'asset_eol_date',
                'value'    => [
                    'startDate' => '2020-12-01',
                    'endDate'   => '2021-01-31',
                ],
                'operator' => 'contains',
                'logic'    => 'AND',
            ],
            
            [
                'field'    => 'created_by',
                'value'    => [$owner->id],
                'operator' => 'equals',
                'logic'    => 'AND',
            ],
        ];

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', [
            'status'      => '',
            'order_number'=> '',
            'company_id'  => '',
            'status_id'   => '',
            'filter'      => json_encode($filter),
            'search'      => '',
            'sort'        => 'id',
            'order'       => 'asc',
            'offset'      => '0',
            'limit'       => '50',
        ]));


        $response->assertOk()->assertJsonStructure(['total','rows']);

        $ids = collect($response->json('rows'))->pluck('id')->all();
        $this->assertSame([$assetA->id], $ids, 'Es darf nur assetA enthalten sein.');
    }

}
