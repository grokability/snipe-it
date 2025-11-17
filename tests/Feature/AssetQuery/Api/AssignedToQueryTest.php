<?php

namespace Tests\Feature\AssetQuery\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class AssignedToQueryTest extends TestCase
{
    use RefreshDatabase;

    // @TODO
    // Location contains
    // Location contains not

    public function testFilterAssetsEmptyValue(): void
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => ''
                ],
                'operator' => 'contains',
                'logic' => 'AND'
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 4)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ])
            ->assertJsonFragment([
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $parentAssetA->id,
            ])
            ->assertJsonFragment([
                'id' => $parentAssetB->id,
            ]);
    }

    public function testFilterAssetsInvalidType(): void
    {
        $locationA = Location::factory()->create(['name' => 'Stockholm']);
        $locationB = Location::factory()->create(['name' => 'Copenhagen']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => 'invalid',
                    'value' => ''
                ],
                'operator' => 'contains',
                'logic' => 'AND'
            ],
        ];

        // Expect the API to return a server error for an invalid type
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
            ->assertServerError();
    }

    public function testFilterAssetsUserContainsValue(): void
    {
        $userA = User::factory()->create(['first_name' => 'Gorpzack', 'last_name' => 'Sootsnort']);
        $userB = User::factory()->create(['first_name' => 'Skratcha', 'last_name' => 'Funguspike']);

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => User::class,
                    'value' => $userB->first_name . ' ' . $userB->last_name,
                ],
                'operator' => 'contains',
                'logic' => 'AND'
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }

    public function testFilterAssetsAssetEqualsValue(): void
    {
        $parentAssetA = Asset::factory()->create(['asset_tag' => 'pc01', 'name' => 'Server']);
        $parentAssetB = Asset::factory()->create(['asset_tag' => 'srv01', 'name' => 'Desktop']);

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $parentAssetB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Asset::class,
                    'value' => $parentAssetA->asset_tag
                ],
                'operator' => 'equals',
                'logic' => 'AND'
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $assetA->id,
            ]);
    }

    public function testFilterAssetsLocationsContainsValue(): void
    {
        $locationA = Location::factory()->create(['name' => 'Berlin']);
        $locationB = Location::factory()->create(['name' => 'Vienna']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => 'Vie'
                ],
                'operator' => 'contains',
                'logic' => 'AND'
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }

    public function testFilterAssetsLocationsNotContainsValue(): void
    {
        $locationA = Location::factory()->create(['name' => 'Paris']);
        $locationB = Location::factory()->create(['name' => 'London']);

        $assetA = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationB->id]);

        $filter = [
            [
                'field' => 'assigned_to',
                'value' => [
                    'type' => Location::class,
                    'value' => 'Pa'
                ],
                'operator' => 'contains',
                'logic' => 'NOT'
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 1)->etc())
            ->assertJsonFragment([
                'id' => $assetB->id,
            ]);
    }
}
