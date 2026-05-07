<?php

namespace Tests\Feature\Checkouts\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class ComponentCheckoutTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $component = Component::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.components.checkout', $component->id))
            ->assertForbidden();
    }

    public function test_cannot_checkout_non_existent_component()
    {
        $this->actingAsForApi(User::factory()->checkoutComponents()->create())
            ->postJson(route('api.components.checkout', 1000))
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre('Component does not exist.');
    }

    public function test_checking_out_component_requires_valid_fields()
    {
        $component = Component::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutComponents()->create())
            ->postJson(route('api.components.checkout', $component->id), [
                //
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertPayloadContains('assigned_to')
            ->assertPayloadContains('assigned_qty');
    }

    public function test_cannot_checkout_component_if_requested_amount_is_more_than_component_quantity()
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create(['qty' => 2]);

        $this->actingAsForApi(User::factory()->checkoutComponents()->create())
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 3,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('admin/components/message.checkout.unavailable', ['remaining' => 2, 'requested' => 3]));
    }

    public function test_cannot_checkout_component_if_requested_amount_is_more_than_what_is_remaining()
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create(['qty' => 2]);
        $component->assets()->attach($component->id, [
            'component_id' => $component->id,
            'created_at' => Carbon::now(),
            'assigned_qty' => 1,
            'asset_id' => $asset->id,
        ]);

        $this->actingAsForApi(User::factory()->checkoutComponents()->create())
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 3,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_can_checkout_component()
    {
        $user = User::factory()->checkoutComponents()->create();
        $asset = Asset::factory()->create();
        $component = Component::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertTrue($component->assets->first()->is($asset));
        $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
    }

    public function test_component_checkout_is_logged()
    {
        $user = User::factory()->checkoutComponents()->create();
        $location = Location::factory()->create();
        $asset = Asset::factory()->create(['location_id' => $location->id]);
        $component = Component::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 2,
            ]);

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $user->id,
            'action_type' => 'checkout',
            'target_id' => $asset->id,
            'target_type' => Asset::class,
            'location_id' => $location->id,
            'item_type' => Component::class,
            'item_id' => $component->id,
            'quantity' => 2,
        ]);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userForCompanyA = User::factory()->for($companyA)->create();
        $assetForCompanyB = Asset::factory()->for($companyB)->create();
        $componentForCompanyB = Component::factory()->for($companyB)->create();

        $this->actingAsForApi($userForCompanyA)
            ->postJson(route('api.components.checkout', $componentForCompanyB->id), [
                'assigned_to' => $assetForCompanyB->id,
                'assigned_qty' => 1,
            ])
            ->assertForbidden();
    }
}
