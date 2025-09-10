<?php

namespace Tests\Feature\Accessories\Api;

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

    public function testFilterAssetsAssignedToEmptyString(): void
    {
        $userA = User::factory()->create();
        $locationA = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);

        $filter = ['assigned_to' => ''];

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

    public function testFilterAssetsAssignedToStringWithLocationType(): void
    {
        $assignedToAssetA = Asset::factory()->create();
        $locationA = Location::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $assignedToAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);

        $filter = ['assigned_to' => $locationA->name, 'assigned_type' => Location::class];

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

    public function testFilterAssetsAssignedToMixedArray(): void
    {
        $assignedToAssetA = Asset::factory()->create();
        $locationA = Location::factory()->create();
        $userA = User::factory()->create();

        $assetA = Asset::factory()->create(['assigned_type' => Asset::class, 'assigned_to' => $assignedToAssetA->id]);
        $assetB = Asset::factory()->create(['assigned_type' => Location::class, 'assigned_to' => $locationA->id]);
        $assetC = Asset::factory()->create(['assigned_type' => User::class, 'assigned_to' => $userA->id]);

        $filter = [
            'assigned_to' => [
                ['assigned_to' => $userA->id, 'assignedType' => User::class],
                ['assigned_to' => $assignedToAssetA->id, 'assignedType' => Asset::class],
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
                'id' => $assetB->id,
            ])
            ->assertJsonFragment([
                'id' => $assetC->id,
            ]);
    }
}
