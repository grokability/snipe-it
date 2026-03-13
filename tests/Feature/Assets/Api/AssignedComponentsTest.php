<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AssignedComponentsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assets.assigned_components', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_adheres_to_company_scoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $asset = Asset::factory()->for($companyA)->create();

        $user = User::factory()->for($companyB)->viewAssets()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.assets.assigned_components', $asset))
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre('Asset not found');
    }

    public function test_can_get_components_assigned_to_specific_asset()
    {
        $unassociatedComponent = Component::factory()->create();

        $asset = Asset::factory()->hasComponents(2)->create();

        $componentsAssignedToAsset = $asset->components;

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.assigned_components', $asset))
            ->assertOk()
            ->assertResponseContainsInRows($componentsAssignedToAsset)
            ->assertResponseDoesNotContainInRows($unassociatedComponent)
            ->assertJson(function (AssertableJson $json) {
                $json->where('total', 2)
                    ->count('rows', 2)
                    ->etc();
            });
    }

    public function test_adheres_to_offset_and_limit()
    {
        $asset = Asset::factory()->hasComponents(2)->create();

        $componentsAssignedToAsset = $asset->components;

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.assigned_components', [
                'asset' => $asset,
                'offset' => 1,
                'limit' => 1,
            ]))
            ->assertOk()
            ->assertResponseDoesNotContainInRows($componentsAssignedToAsset->first())
            ->assertResponseContainsInRows($componentsAssignedToAsset->last())
            ->assertJson(function (AssertableJson $json) {
                $json->where('total', 2)
                    ->count('rows', 1)
                    ->etc();
            });
    }
}
