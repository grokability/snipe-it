<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\Company;
use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexAssetModelsTest extends TestCase
{
    public function testViewingAssetModelIndexRequiresAuthentication()
    {
        $this->getJson(route('api.models.index'))->assertRedirect();
    }

    public function testViewingAssetModelIndexRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.models.index'))
            ->assertForbidden();
    }

    public function testAssetModelIndexReturnsExpectedAssetModels()
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function testAssetModelIndexSearchReturnsExpectedAssetModels()
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc());
    }

}
