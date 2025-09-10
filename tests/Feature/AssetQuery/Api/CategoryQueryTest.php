<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class CategoryQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsCategoryEmptyString(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => ''];

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
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        $filter = ['category' => $categoryA->name];

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
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $categoryC = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);
        $modelC = AssetModel::factory()->create(['category_id' => $categoryC->id]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = [
            'category' => [
                $categoryA->id,
                $categoryC->id,
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
