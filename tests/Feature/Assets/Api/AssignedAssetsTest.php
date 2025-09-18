<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AssignedAssetsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assets.assigned_assets', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_adheres_to_company_scoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $asset = Asset::factory()->for($companyA)->create();

        $user = User::factory()->for($companyB)->viewAssets()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.assets.assigned_assets', $asset))
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre('Asset not found');
    }

    public function test_can_get_assets_assigned_to_specific_asset()
    {
        $unassociatedAsset = Asset::factory()->create();

        $asset = Asset::factory()->hasAssignedAssets(2)->create();

        $assetsAssignedToAsset = Asset::where([
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ])->get();

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.assigned_assets', $asset))
            ->assertOk()
            ->assertResponseContainsInRows($assetsAssignedToAsset, 'serial')
            ->assertResponseDoesNotContainInRows($unassociatedAsset, 'serial')
            ->assertJson(function (AssertableJson $json) {
                $json->where('total', 2)
                    ->count('rows', 2)
                    ->etc();
            });
    }

    public function test_adheres_to_offset_and_limit()
    {
        $asset = Asset::factory()->hasAssignedAssets(2)->create();

        $assetsAssignedToAsset = Asset::where([
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ])->get();

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.assigned_assets', [
                'asset' => $asset,
                'offset' => 1,
                'limit' => 1,
            ]))
            ->assertOk()
            ->assertResponseDoesNotContainInRows($assetsAssignedToAsset->first(), 'serial')
            ->assertResponseContainsInRows($assetsAssignedToAsset->last(), 'serial')
            ->assertJson(function (AssertableJson $json) {
                $json->where('total', 2)
                    ->count('rows', 1)
                    ->etc();
            });
    }
}
