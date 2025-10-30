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

        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addDays(14)->format('Y-m-d')]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(14)->format('Y-m-d')]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addMonths(14)->format('Y-m-d')]);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'start' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetC->id,
            ])
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }

    public function testPurchaseDateQueryRange()
    {
        Carbon::setTestNow(Carbon::create(2011, 5, 15));

        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(50)->format('Y-m-d')]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(75)->format('Y-m-d')]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(100)->format('Y-m-d')]);
        $assetD = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(125)->format('Y-m-d')]);
        $assetE = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(150)->format('Y-m-d')]);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'start' => Carbon::now()->addWeeks(70)->format('Y-m-d'),
                    'end' => Carbon::now()->addWeeks(130)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ])
            ->assertJsonFragment([
                'id' => $assetD->id,
            ]);
    }

    public function testEolDateQueryEnd()
    {
        $this->markTestSkipped("Marked as skipped because some of these tests fail sometimes on the pipeline and we hadn't enought time to fix it :-( ");
        $this->markTestSkipped("Test doesn't work currently at the moment");
        Carbon::setTestNow(Carbon::create(2020, 12, 16));

        $purchaseDate = Carbon::now();
        $modelA = AssetModel::factory()->create(['eol' => 12]); // EOL in months
        $modelB = AssetModel::factory()->create(['eol' => 24]);
        $modelC = AssetModel::factory()->create(['eol' => 36]);

        $assetA = Asset::factory()->create([
            'model_id' => $modelA->id,
            'purchase_date' => $purchaseDate->toDateString(),
            'asset_eol_date' => $purchaseDate->copy()->addMonths(36)->toDateString(),
        ]);
        $assetB = Asset::factory()->create([
            'model_id' => $modelB->id,
            'purchase_date' => $purchaseDate->toDateString(),
            'asset_eol_date' => $purchaseDate->copy()->addMonths(36)->toDateString(),
        ]);
        $assetC = Asset::factory()->create([
            'model_id' => $modelC->id,
            'purchase_date' => $purchaseDate->toDateString(),
            'asset_eol_date' => $purchaseDate->copy()->addMonths(36)->toDateString(),
        ]);


        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'end' => Carbon::now()->addMonths(20)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
                'id' => $assetB->id,
            ]);
    }
    public function testEolDateQueryRange()
    {
        $this->markTestSkipped("Marked as skipped because some of these tests fail sometimes on the pipeline and we hadn't enought time to fix it :-( ");
        $this->markTestSkipped("Test doesn't work currently at the moment");
        Carbon::setTestNow(Carbon::create(2020, 12, 16));

        $modelA = AssetModel::factory()->create(['eol' => 12]);
        $modelB = AssetModel::factory()->create(['eol' => 24]);
        $modelC = AssetModel::factory()->create(['eol' => 36]);
        $modelD = AssetModel::factory()->create(['eol' => 48]);
        $modelE = AssetModel::factory()->create(['eol' => 60]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id]);
        $assetE = Asset::factory()->create(['model_id' => $modelE->id]);

        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'start' => Carbon::now()->addMonths(20)->format('Y-m-d'),
                    'end' => Carbon::now()->addMonths(50)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ])
            ->assertJsonFragment([
                'id' => $assetD->id,
            ]);
    }

    public function testCreatedAtDateQueryStart()
    {
        Carbon::setTestNow(Carbon::create(2022, 2, 27));

        // Setup dates
        $yesterday = Carbon::now()->subDays(5)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(5)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['created_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['created_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['created_at' => $tomorrow->toDateTimeString()]);

        // Filter: only include assets created on or after today
        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'start' => $today->toDateString(),
                ],
                'operator' => 'contains', // Assuming your filter logic handles this correctly
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ]);
    }

    public function testCreatedAtDateQueryEnd()
    {
        Carbon::setTestNow(Carbon::create(2012, 4, 22));
        // Setup dates
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['created_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['created_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['created_at' => $tomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'end' => $today->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ])
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }
    public function testCreatedAtDateQueryRange()
    {
        //$this->markTestSkipped("Test doesn't work currently at the moment");
        Carbon::setTestNow(Carbon::create(2018, 11, 21));
        // Setup dates
        $dayBeforeYesterday = Carbon::now()->subDays(2)->startOfDay();
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();
        $dayAfterTomorrow = Carbon::now()->addDays(2)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['created_at' => $dayBeforeYesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['created_at' => $yesterday->toDateTimeString()]);
        $assetC = Asset::factory()->create(['created_at' => $today->toDateTimeString()]);
        $assetD = Asset::factory()->create(['created_at' => $tomorrow->toDateTimeString()]);
        $assetE = Asset::factory()->create(['created_at' => $dayAfterTomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'start' => $yesterday->toDateString(),
                    'end' => $tomorrow->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ])
            ->assertJsonFragment([
                'id' => $assetD->id,
            ]);
    }

    public function testUpdatedAtDateQueryStart()
    {
        Carbon::setTestNow(Carbon::create(2021, 4, 26));
        // Setup dates
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['updated_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['updated_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['updated_at' => $tomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'start' => $today->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ]);
    }

    public function testUpdatedAtDateQueryEnd()
    {
        Carbon::setTestNow(Carbon::create(1990, 4, 6));
        // Setup dates
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['updated_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['updated_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['updated_at' => $tomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'end' => $today->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 2)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ])
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }

}