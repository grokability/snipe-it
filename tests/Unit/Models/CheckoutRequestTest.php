<?php

namespace Tests\Unit\Models;

use App\Models\CheckoutRequest;
use Tests\TestCase;

class CheckoutRequestTest extends TestCase
{
    public function test_checkout_request_soft_deleted_when_requested_asset_soft_deleted()
    {
        $checkoutRequest = CheckoutRequest::factory()->forAsset()->create();

        $requestedAsset = $checkoutRequest->requestedItem;

        $requestedAsset->delete();

        $this->assertSoftDeleted($checkoutRequest->fresh());
    }

    public function test_checkout_request_deleted_when_requested_asset_force_deleted()
    {
        $checkoutRequest = CheckoutRequest::factory()->forAsset()->create();

        $requestedAsset = $checkoutRequest->requestedItem;

        $requestedAsset->forceDelete();

        $this->assertDatabaseMissing('checkout_requests', ['id' => $checkoutRequest->id]);
    }

    public function test_checkout_request_soft_deleted_when_requested_model_soft_deleted()
    {
        $checkoutRequest = CheckoutRequest::factory()->forAssetModel()->create();

        $requestedAssetModel = $checkoutRequest->requestedItem;

        $requestedAssetModel->delete();

        $this->assertSoftDeleted($checkoutRequest->fresh());
    }

    public function test_checkout_request_deleted_when_requested_model_force_deleted()
    {
        $checkoutRequest = CheckoutRequest::factory()->forAssetModel()->create();

        $requestedAsset = $checkoutRequest->requestedItem;

        $requestedAsset->forceDelete();

        $this->assertDatabaseMissing('checkout_requests', ['id' => $checkoutRequest->id]);
    }

    public function test_checkout_request_soft_deleted_when_requesting_user_soft_deleted()
    {
        $checkoutRequest = CheckoutRequest::factory()->forAsset()->create();

        $requestingUser = $checkoutRequest->user;

        $requestingUser->delete();

        $this->assertSoftDeleted($checkoutRequest->fresh());
    }

    public function test_checkout_request_deleted_when_requesting_user_force_deleted()
    {
        $checkoutRequest = CheckoutRequest::factory()->forAsset()->create();

        $requestingUser = $checkoutRequest->user;

        $requestingUser->forceDelete();

        $this->assertDatabaseMissing('checkout_requests', ['id' => $checkoutRequest->id]);
    }
}
