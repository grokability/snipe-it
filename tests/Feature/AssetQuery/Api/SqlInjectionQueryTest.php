<?php

/*
* These testcases are trying to inject SQL through the advanced search api.
*/
namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SqlInjectionQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsAssignedToAssetSqlInjectionAttempt(): void
    {
        // Setup: Two legitimate categories, models, and assets
        $assignedAssetA = Asset::factory()->create();
        $locationA = Location::factory()->create();
        $userA = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $assignedAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);

        // Attempted SQL injection payload in the filter
        $sqlInjectionString = "' OR '1'='1";

        $filter = ['assigned_to' => $sqlInjectionString, 'assigned_type' => Location::class];

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
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('total', 0)
                    ->where('rows', [])
                    ->etc()
            );
    }
    public function testFilterAssetsCategorySqlInjectionAttempt(): void
    {
        // Setup: Two legitimate categories, models, and assets
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $modelA = AssetModel::factory()->create(['category_id' => $categoryA->id]);
        $modelB = AssetModel::factory()->create(['category_id' => $categoryB->id]);

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);

        // Attempted SQL injection payload in the filter
        $sqlInjectionString = "' OR '1'='1";

        $filter = ['category' => $sqlInjectionString];

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
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('total', 0)
                    ->where('rows', [])
                    ->etc()
            );
    }

}
