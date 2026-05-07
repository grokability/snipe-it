<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexAssetModelsTest extends TestCase
{
    public function test_viewing_asset_model_index_requires_authentication()
    {
        $this->getJson(route('api.models.index'))->assertRedirect();
    }

    public function test_viewing_asset_model_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.models.index'))
            ->assertForbidden();
    }

    public function test_asset_model_index_returns_expected_asset_models()
    {
        AssetModel::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.models.index', [
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_model_index_search_returns_expected_asset_models()
    {
        AssetModel::factory()->count(3)->create();
        AssetModel::factory()->count(1)->create(['name' => 'Test Model']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.models.index', [
                    'search' => 'Test Model',
                    'sort' => 'id',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    public function test_asset_model_index_filter_can_search_computed_count_aliases()
    {
        $this->markIncompleteIfSqlite('This test is not compatible with SQLite');
        $targetModel = AssetModel::factory()->create(['name' => 'Two Assets Model']);
        $otherModel = AssetModel::factory()->create(['name' => 'One Asset Model']);

        Asset::factory()->count(2)->create(['model_id' => $targetModel->id]);
        Asset::factory()->create(['model_id' => $otherModel->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.models.index', [
                'filter' => '{"assets_count":"2"}',
                'sort' => 'id',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment(['name' => 'Two Assets Model']);
    }
}
