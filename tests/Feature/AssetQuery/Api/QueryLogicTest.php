<?php

namespace Tests\Feature\AssetQuery\Api;

use Tests\TestCase;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use App\Models\Manufacturer;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueryLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function assertFilterResult(array $filter, $user, array $expectedIds)
    {
        return $this->actingAsForApi($user)
            ->getJson(route('api.assets.index', ['filter' => json_encode($filter)]))
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('total')
                     ->has('rows', count($expectedIds))->etc()
            )
            ->assertJsonPath('rows.*.id', $expectedIds);
    }

    public function testFilterAssetsWithModelAndManufacturerCombinations(): void
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $dell = Manufacturer::factory()->create(['name' => 'Dell']);

        $macbook = AssetModel::factory()->create(['name' => 'MacBook Pro', 'manufacturer_id' => $apple->id]);
        $xps = AssetModel::factory()->create(['name' => 'XPS 15', 'manufacturer_id' => $dell->id]);

        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetDell = Asset::factory()->create(['model_id' => $xps->id]);

        $user = User::factory()->superuser()->create();

        // -- Case 1: "macbook" AND "Apple" => Returns MacBook
        $filter1 = [
            [
                'field' => 'model',
                'value' => ['macbook'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
        ];

        $this->assertFilterResult($filter1, $user, (array)$assetMacbook->id);

        // -- Case 2: "macbook" AND NOT "Apple" =>  Returns nothing
        $filter2 = [
            [
                'field' => 'model',
                'value' => ['macbook'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'NOT',
            ],
        ];

        $this->assertFilterResult($filter2, $user, []);

        // -- Case 3: "macb" AND "Apple" => Returns MacBook (partial match)
        $filter3 = [
            [
                'field' => 'model',
                'value' => ['macb'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
        ];

        $this->assertFilterResult($filter3, $user, (array)$assetMacbook->id)
            ->assertJsonMissingExact(['rows' => [['id' => $assetDell->id]]]);

        // -- Case 4: "macb" AND NOT "Apple" => Returns nothing
        $filter4 = [
            [
                'field' => 'model',
                'value' => ['macb'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => ['Apple'],
                'operator' => 'contains',
                'logic' => 'NOT',
            ],
        ];

        $this->assertFilterResult($filter4, $user, []);
    }

    public function testFilterModelContainsBookButNotAppleManufacturer(): void
    {
        // Create manufacturers
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $microsoft = Manufacturer::factory()->create(['name' => 'Microsoft']);
        $asus = Manufacturer::factory()->create(['name' => 'Asus']);

        // Create models
        $macbook = AssetModel::factory()->create(['name' => 'MacBook', 'manufacturer_id' => $apple->id]);
        $surfacebook = AssetModel::factory()->create(['name' => 'SurfaceBook', 'manufacturer_id' => $microsoft->id]);
        $zenbook = AssetModel::factory()->create(['name' => 'ZenBook', 'manufacturer_id' => $asus->id]);

        // Create assets
        $assetMacbook = Asset::factory()->create(['model_id' => $macbook->id]);
        $assetSurfacebook = Asset::factory()->create(['model_id' => $surfacebook->id]);
        $assetZenbook = Asset::factory()->create(['model_id' => $zenbook->id]);

        // Create user
        $user = User::factory()->superuser()->create();

        // Build filter: model contains "book", manufacturer NOT Apple
        $filter = [
            [
                'field' => 'model',
                'value' => ['book'],
                'operator' => 'contains',
                'logic' => 'AND',
            ],
            [
                'field' => 'manufacturer',
                'value' => [$apple->id],
                'operator' => 'equals',
                'logic' => 'NOT',
            ],
        ];

        // Make request and assert
        $this->actingAsForApi($user)
            ->getJson(route('api.assets.index', ['filter' => json_encode($filter)]))
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json
                ->has('total')
                ->has('rows', 2)
                ->etc()
            )
            ->assertJsonFragment(['id' => $assetSurfacebook->id])
            ->assertJsonFragment(['id' => $assetZenbook->id])
            ->assertJsonMissing(['id' => $assetMacbook->id]);
    }
}

