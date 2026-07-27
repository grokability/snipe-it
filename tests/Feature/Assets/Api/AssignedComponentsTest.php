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

        $user = User::factory()->forCompany($companyB)->viewAssets()->create();

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

        // Needs viewComponents too - the transformer now omits components
        // the caller can't view, so a viewAssets-only actor would see an
        // empty list under the fix.
        $response = $this->actingAsForApi(User::factory()->viewAssets()->viewComponents()->create())
            ->getJson(route('api.assets.assigned_components', $asset))
            ->assertOk()
            // ->assertResponseContainsInRows($componentsAssignedToAsset)
            // ->assertResponseDoesNotContainInRows($unassociatedComponent)
            ->assertJson(function (AssertableJson $json) {
                $json->where('total', 2)
                    ->count('rows', 2)
                    ->etc();
            });
    }

    /**
     * Security regression pin. Before the fix in
     * AssetsTransformer::transformCheckedoutComponents, a caller with
     * assets.view but not components.view could read the component's
     * name / assigned_qty / note through this endpoint (direct
     * GET /api/v1/components/{id} correctly returned 403). Now denied
     * rows are omitted entirely - the response neither reveals the
     * component's id nor that it exists at all. total is decremented so
     * it doesn't leak "there are N components you can't see" either.
     */
    public function test_does_not_leak_component_details_when_caller_lacks_components_view(): void
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create(['name' => 'Hidden SSD Component']);
        $component->assets()->attach($component->id, [
            'component_id' => $component->id,
            'assigned_qty' => 2,
            'created_by' => User::factory()->superuser()->create()->id,
            'asset_id' => $asset->id,
            'note' => 'sensitive component note',
        ]);

        // viewAssets only. No viewComponents.
        $actor = User::factory()->viewAssets()->create();

        $this->actingAsForApi($actor)
            ->getJson(route('api.assets.assigned_components', $asset))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonCount(0, 'rows');
    }

    /**
     * Positive-path sanity: a caller who DOES have components.view still
     * gets the full component details. Guards against the info-disclosure
     * guard accidentally stripping data from authorized callers.
     */
    public function test_returns_full_component_details_when_caller_has_components_view(): void
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create(['name' => 'Visible SSD']);
        $component->assets()->attach($component->id, [
            'component_id' => $component->id,
            'assigned_qty' => 2,
            'created_by' => User::factory()->superuser()->create()->id,
            'asset_id' => $asset->id,
            'note' => 'component note',
        ]);

        $actor = User::factory()->viewAssets()->viewComponents()->create();

        $this->actingAsForApi($actor)
            ->getJson(route('api.assets.assigned_components', $asset))
            ->assertOk()
            ->assertJsonPath('rows.0.name.id', $component->id)
            ->assertJsonPath('rows.0.name.name', 'Visible SSD')
            ->assertJsonPath('rows.0.assigned_qty', 2)
            ->assertJsonPath('rows.0.note', 'component note');
    }

    /**
     * Distinct code path from the dedicated /assigned/components endpoint:
     * the main asset detail response inlines full component blocks under
     * the `components` key when the caller passes `?components=true`.
     * Same class of leak, same fix pattern applied inline in
     * AssetsTransformer::transformAsset. Denied components are omitted
     * from the response entirely.
     */
    public function test_show_asset_with_components_true_query_omits_components_when_denied(): void
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create(['name' => 'Hidden CPU', 'purchase_cost' => 500]);
        $component->assets()->attach($component->id, [
            'component_id' => $component->id,
            'assigned_qty' => 3,
            'created_by' => User::factory()->superuser()->create()->id,
            'asset_id' => $asset->id,
        ]);

        $actor = User::factory()->viewAssets()->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.assets.show', ['hardware' => $asset->id, 'components' => 'true']))
            ->assertOk();

        $this->assertSame([], $response->json('components'), 'Denied components must be absent entirely - not even id / pivot_id');
    }
}
