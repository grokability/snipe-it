<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterAssetsCompanyEmptyString(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        // Assets mit direkter company_id
        $assetA = Asset::factory()->create([
            'company_id' => $companyA->id,
        ]);
        $assetB = Asset::factory()->create([
            'company_id' => $companyB->id,
        ]);

        $filter = ['company' => ''];

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

    public function testFilterAssetsCompanyString(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        // Assets mit direkter company_id
        $assetA = Asset::factory()->create([
            'company_id' => $companyA->id,
        ]);
        $assetB = Asset::factory()->create([
            'company_id' => $companyB->id,
        ]);

        $filter = ['company' => $companyB->name];

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

    public function testFilterAssetsCompanyArray(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $companyC = Company::factory()->create();

        // Assets mit direkter company_id
        $assetA = Asset::factory()->create([
            'company_id' => $companyA->id,
        ]);
        $assetB = Asset::factory()->create([
            'company_id' => $companyB->id,
        ]);
        $assetC = Asset::factory()->create([
            'company_id' => $companyC->id,
        ]);

        $filter = ['company' => [$companyB->name, $companyC->name]];

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