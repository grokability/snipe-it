<?php

namespace Tests\Feature\Console;

use App\Models\CheckoutRequest;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class CleanOldCheckoutRequestsTest extends TestCase
{
    private CheckoutRequest $validRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validRequest = CheckoutRequest::factory()->forAsset()->create();
    }

    public function test_clean_old_checkout_requests_command_for_soft_deleted_asset()
    {
        $requestForSoftDeletedAsset = CheckoutRequest::factory()->forAsset()->create();
        Model::withoutEvents(fn() => $requestForSoftDeletedAsset->requestedItem->delete());

        $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

        $this->assertNotSoftDeleted($this->validRequest);
        $this->assertSoftDeleted($requestForSoftDeletedAsset->fresh());
    }

    public function test_clean_old_checkout_requests_command_for_missing_asset()
    {
        $requestForMissingAsset = CheckoutRequest::factory()->forAsset()->create(['requestable_id' => 99999999]);

        $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

        $this->assertNotSoftDeleted($this->validRequest);
        $this->assertDatabaseMissing('checkout_requests', ['requestable_id' => $requestForMissingAsset->requestable_id]);
    }

    public function test_clean_old_checkout_requests_command_for_soft_deleted_model()
    {
        $requestForSoftDeletedAssetModel = CheckoutRequest::factory()->forAssetModel()->create();
        Model::withoutEvents(fn() => $requestForSoftDeletedAssetModel->requestedItem->delete());

        $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

        $this->assertNotSoftDeleted($this->validRequest);
        $this->assertSoftDeleted($requestForSoftDeletedAssetModel->fresh());
    }

    public function test_clean_old_checkout_requests_command_for_missing_model()
    {
        $requestForMissingModel = CheckoutRequest::factory()->forAssetModel()->create(['requestable_id' => 99999999]);

        $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

        $this->assertNotSoftDeleted($this->validRequest);
        $this->assertDatabaseMissing('checkout_requests', ['requestable_id' => $requestForMissingModel->requestable_id]);
    }

    public function test_clean_old_checkout_requests_command_for_soft_deleted_user()
    {
        $requestForSoftDeletedUser = CheckoutRequest::factory()->forAsset()->create();
        Model::withoutEvents(fn() => $requestForSoftDeletedUser->user->delete());

        $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

        $this->assertNotSoftDeleted($this->validRequest);
        $this->assertSoftDeleted($requestForSoftDeletedUser->fresh());
    }

    public function test_clean_old_checkout_requests_command_for_missing_user()
    {
        $requestForMissingUser = CheckoutRequest::factory()->forAsset()->create(['user_id' => 99999999]);

        $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

        $this->assertNotSoftDeleted($this->validRequest);
        $this->assertDatabaseMissing('checkout_requests', ['user_id' => $requestForMissingUser->user_id]);
    }
}
