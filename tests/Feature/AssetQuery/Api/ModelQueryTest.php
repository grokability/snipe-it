<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ModelQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsCategoryEmptyString(): void
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model' => ''];

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

    public function testFilterAssetsCategoryString(): void
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['model' => $modelA->id];

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

    public function testFilterAssetsCategoryArray(): void
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $modelC = AssetModel::factory()->create();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = [
            'model' => [
                $modelA->id,
                $modelC->id,
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
