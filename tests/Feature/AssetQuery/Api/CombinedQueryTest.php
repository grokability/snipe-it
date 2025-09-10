<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class CombinedQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetModelLocationArrayManufacturerArray(): void
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $locationC = Location::factory()->create();
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);
        $assetE = Asset::factory()->create(['location_id' => $locationC->id]);

        $filter = [
            'model' => [$modelA->name, $modelB->name],
            'location' => [$locationA->name, $locationB->name],
            'manufacturer' => [$manufacturerA->name, $manufacturerB->name]
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 4)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ])
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

    public function testFilterWithDuplicateValuesReturnsUniqueResults(): void
    {
        $modelA = AssetModel::factory()->create();
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $filter = ['model' => [$modelA->name, $modelA->name]];

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

    public function testFilterAssetsConflictingFiltersReturnNone(): void
    {
        $modelA = AssetModel::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();
        $modelA->manufacturer_id = $manufacturerB->id + 1; // Not matching
        $modelA->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $filter = ['model' => $modelA->name, 'manufacturer' => 'NonexistentManufacturer'];

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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 0)->etc());
    }

    public function testFilterAssetAllFiltersAsStrings(): void
    {
        $model = AssetModel::factory()->create();
        $location = Location::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $status = Statuslabel::factory()->create();

        $model->manufacturer_id = $manufacturer->id;
        $model->save();

        $assetA = Asset::factory()->create([
            'model_id' => $model->id,
            'location_id' => $location->id,
            'status_id' => $status->id
        ]);
        $assetB = Asset::factory()->create(); // Should not match

        $filter = [
            'model' => $model->name,
            'location' => $location->name,
            'manufacturer' => $manufacturer->name,
            'status_label' => $status->name,
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

    public function testFilterAssetModelLocationManufacturer(): void
    {
        $modelA = AssetModel::factory()->create();
        $modelB = AssetModel::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $manufacturerA = Manufacturer::factory()->create();
        $manufacturerB = Manufacturer::factory()->create();

        $modelA->manufacturer_id = $manufacturerA->id;
        $modelA->save();
        $modelB->manufacturer_id = $manufacturerB->id;
        $modelB->save();

        $assetA = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelA->id, 'location_id' => $locationB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationA->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelB->id, 'location_id' => $locationB->id]);

        $filter = [
            'model' => $modelA->name,
            'location' => $locationA->name,
            'manufacturer' => $manufacturerA->name
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
