<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;


class StatusLabelQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsCategoryEmptyString(): void
    {
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['status_id' => $statusPending->id]);
        $assetB = Asset::factory()->create(['status_id' => $statusArchived->id]);

        $filter = ['status_label' => ''];

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
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        // Assets
        $assetA = Asset::factory()->create(['status_id' => $statusPending->id]);
        $assetB = Asset::factory()->create(['status_id' => $statusArchived->id]);

        $filter = ['status_label' => $statusPending->id];

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
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();
        $statusBroken = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create(['status_id' => $statusPending->id]);
        $assetB = Asset::factory()->create(['status_id' => $statusArchived->id]);
        $assetC = Asset::factory()->create(['status_id' => $statusBroken->id]);

        $filter = [
            'status_label' => [
                $statusPending->id,
                $statusBroken->id,
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
