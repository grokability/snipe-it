<?php

namespace Tests\Feature\AssetQuery\Api;

use App\Models\Asset;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SupplierQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsSupplierEmptyString(): void
    {
        $supplierA = Supplier::factory()->create();
        $statusArchived = Supplier::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['supplier_id' => $supplierA->id]);
        $assetB = Asset::factory()->create(['supplier_id' => $statusArchived->id]);

        $filter = ['supplier' => ''];

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

    public function testFilterAssetsSupplierString(): void
    {
        $supplierA = Supplier::factory()->create();
        $supplierB = Supplier::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['supplier_id' => $supplierA->id]);
        $assetB = Asset::factory()->create(['supplier_id' => $supplierB->id]);

        $filter = ['supplier' => $supplierA->id];

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

    public function testFilterAssetsSupplierArray(): void
    {
        $supplierA = Supplier::factory()->create();
        $supplierB = Supplier::factory()->create();
        $supplierC = Supplier::factory()->create();

        $assetA = Asset::factory()->create(['supplier_id' => $supplierA->id]);
        $assetB = Asset::factory()->create(['supplier_id' => $supplierB->id]);
        $assetC = Asset::factory()->create(['supplier_id' => $supplierC->id]);

        $filter = [
            [
                'field' => 'supplier',
                'value' => [$supplierA->id, $supplierC->id],
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
