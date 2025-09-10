<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ManufacturerQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsManufacturerEmptyString(): void
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => ''];

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

    public function testFilterAssetsManufacturerString(): void
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['manufacturer' => $manufacturerA->name];

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

    public function testFilterAssetsManufacturerArray(): void
    {
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();
        $manufacturerC = Manufacturer::factory()->create();

        $modelA = AssetModel::factory()->create(['manufacturer_id' => $manufacturerA->id]);
        $modelB = AssetModel::factory()->create(['manufacturer_id' => $manufacturerB->id]);
        $modelC = AssetModel::factory()->create(['manufacturer_id' => $manufacturerC->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = [
            'manufacturer' => [
                $manufacturerA->id,
                $manufacturerC->id,
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
