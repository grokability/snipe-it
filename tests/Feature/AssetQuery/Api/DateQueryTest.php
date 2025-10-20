<?php
namespace Tests\Feature\AssetQuery\Api\DateQuery;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateQueryTest extends TestCase
{

    use RefreshDatabase;

    public function testPurchaseDateQueryStart()
    {
        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => '2025-01-23']);
        $assetB = Asset::factory()->create(['purchase_date' => '2025-03-24']);
        $assetC = Asset::factory()->create(['purchase_date' => '2025-05-25']);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'start' => '2025-03-20',
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

    public function testPurchaseDateQueryRange()
    {
        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => '2025-01-23']);
        $assetB = Asset::factory()->create(['purchase_date' => '2025-03-24']);
        $assetC = Asset::factory()->create(['purchase_date' => '2025-05-25']);
        $assetD = Asset::factory()->create(['purchase_date' => '2025-07-26']);
        $assetE = Asset::factory()->create(['purchase_date' => '2025-09-27']);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'start' => '2025-03-20',
                    'end' => '2025-07-30',
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
        // Assets
        $assetA = Asset::factory()->create(['asset_eol_date' => '2025-01-23']);
        $assetB = Asset::factory()->create(['asset_eol_date' => '2025-03-24']);
        $assetC = Asset::factory()->create(['asset_eol_date' => '2025-05-25']);

        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'end' => '2025-02-20',
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
                'id' => $assetA->id,
            ]);
    }
    public function testEolDateQueryRange()
    {
        // Assets
        $assetA = Asset::factory()->create(['asset_eol_date' => '2025-01-23']);
        $assetB = Asset::factory()->create(['asset_eol_date' => '2025-03-24']);
        $assetC = Asset::factory()->create(['asset_eol_date' => '2025-05-25']);
        $assetD = Asset::factory()->create(['asset_eol_date' => '2025-07-26']);
        $assetE = Asset::factory()->create(['asset_eol_date' => '2025-09-27']);

        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'start' => '2025-03-20',
                    'end' => '2025-07-30',
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
        // Assets
        $assetA = Asset::factory()->create(['created_at' => '2025-01-23']);
        $assetB = Asset::factory()->create(['created_at' => '2025-03-24']);
        $assetC = Asset::factory()->create(['created_at' => '2025-05-25']);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'start' => '2025-03-20',
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

    public function testCreatedAtDateQueryEnd()
    {
        // Assets
        $assetA = Asset::factory()->create(['created_at' => '2025-01-23']);
        $assetB = Asset::factory()->create(['created_at' => '2025-03-24']);
        $assetC = Asset::factory()->create(['created_at' => '2025-05-25']);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'end' => '2025-02-20',
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }
    public function testCreatedAtDateQueryRange()
    {
        // Assets
        $assetA = Asset::factory()->create(['created_at' => '2025-01-23']);
        $assetB = Asset::factory()->create(['created_at' => '2025-03-24']);
        $assetC = Asset::factory()->create(['created_at' => '2025-05-25']);
        $assetD = Asset::factory()->create(['created_at' => '2025-07-26']);
        $assetE = Asset::factory()->create(['created_at' => '2025-09-27']);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'start' => '2025-03-20',
                    'end' => '2025-07-30',
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetE));
    }

    public function testUpdatedAtDateQueryStart()
    {
        // Assets
        $assetA = Asset::factory()->create(['updated_at' => '2025-01-23']);
        $assetB = Asset::factory()->create(['updated_at' => '2025-03-24']);
        $assetC = Asset::factory()->create(['updated_at' => '2025-05-25']);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'start' => '2025-03-20',
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

    public function testUpdatedAtDateQueryEnd()
    {
        // Assets
        $assetA = Asset::factory()->create(['updated_at' => '2025-01-23']);
        $assetB = Asset::factory()->create(['updated_at' => '2025-03-24']);
        $assetC = Asset::factory()->create(['updated_at' => '2025-05-25']);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'end' => '2025-02-20',
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
                'id' => $assetA->id,
            ]);
    }

}