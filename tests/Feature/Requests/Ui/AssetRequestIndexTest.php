<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class AssetRequestIndexTest extends TestCase
{
    public function test_requires_permission_to_view_asset_request_index()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('assets.requested'))
            ->assertForbidden();
    }

    public function test_can_view_request_asset_request_index()
    {
        $checkoutRequest = CheckoutRequest::factory()->create();

        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertViewHas('requestedItems')
            ->assertSeeText($checkoutRequest->requestedItem->asset_tag);
    }

    public function test_user_sees_requests_for_assets_in_their_own_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
        $request = CheckoutRequest::factory()->create(['requestable_id' => $assetA->id, 'requestable_type' => Asset::class]);
        $viewer = User::factory()->viewAssets()->create(['company_id' => $companyA->id]);

        $this->actingAs($viewer)
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertSeeText($request->requestedItem->asset_tag);
    }

    public function test_user_does_not_see_requests_for_assets_in_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $crossCompanyRequest = CheckoutRequest::factory()->create(['requestable_id' => $assetB->id, 'requestable_type' => Asset::class]);
        $viewer = User::factory()->viewAssets()->create(['company_id' => $companyA->id]);

        $this->actingAs($viewer)
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertViewHas(
                'requestedItems',
                fn ($items) => $items->doesntContain(fn ($r) => $r->id === $crossCompanyRequest->id)
            );
    }

    public function test_pivot_only_user_does_not_see_requests_for_assets_in_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $crossCompanyRequest = CheckoutRequest::factory()->create(['requestable_id' => $assetB->id, 'requestable_type' => Asset::class]);
        $viewer = User::factory()->viewAssets()->create(['company_id' => null]);
        $viewer->companies()->sync([$companyA->id]);

        $this->actingAs($viewer)
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertViewHas(
                'requestedItems',
                fn ($items) => $items->doesntContain(fn ($r) => $r->id === $crossCompanyRequest->id)
            );
    }

    public function test_floater_mode_makes_null_company_requests_visible_to_company_scoped_users()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->enableFloaterMode();

        [$companyA] = Company::factory()->count(2)->create();

        $floaterAsset = Asset::factory()->create(['company_id' => null]);
        $request = CheckoutRequest::factory()->create(['requestable_id' => $floaterAsset->id, 'requestable_type' => Asset::class]);
        $viewer = User::factory()->viewAssets()->create(['company_id' => $companyA->id]);

        $this->actingAs($viewer)
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertViewHas(
                'requestedItems',
                fn ($items) => $items->contains(fn ($r) => $r->id === $request->id)
            );
    }

    public function test_superuser_sees_requests_across_all_companies()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $requestA = CheckoutRequest::factory()->create(['requestable_id' => $assetA->id, 'requestable_type' => Asset::class]);
        $requestB = CheckoutRequest::factory()->create(['requestable_id' => $assetB->id, 'requestable_type' => Asset::class]);
        $superuser = User::factory()->superuser()->create(['company_id' => $companyA->id]);

        $this->actingAs($superuser)
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertSeeText($requestA->requestedItem->asset_tag)
            ->assertSeeText($requestB->requestedItem->asset_tag);
    }

    public function test_company_scoping_not_enforced_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $request = CheckoutRequest::factory()->create(['requestable_id' => $assetB->id, 'requestable_type' => Asset::class]);
        $viewer = User::factory()->viewAssets()->create(['company_id' => $companyA->id]);

        $this->actingAs($viewer)
            ->get(route('assets.requested'))
            ->assertOk()
            ->assertSeeText($request->requestedItem->asset_tag);
    }
}
