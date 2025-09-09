<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\CheckoutRequest;
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
}
