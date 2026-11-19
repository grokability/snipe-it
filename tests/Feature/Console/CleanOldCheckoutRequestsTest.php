<?php

use App\Models\CheckoutRequest;
use Illuminate\Database\Eloquent\Model;

beforeEach(function () {
    $this->validRequest = CheckoutRequest::factory()->forAsset()->create();
});

test('clean old checkout requests command for soft deleted asset', function () {
    $requestForSoftDeletedAsset = CheckoutRequest::factory()->forAsset()->create();
    Model::withoutEvents(fn () => $requestForSoftDeletedAsset->requestedItem->delete());

    $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

    $this->assertNotSoftDeleted($this->validRequest);
    $this->assertSoftDeleted($requestForSoftDeletedAsset->fresh());
});

test('clean old checkout requests command for missing asset', function () {
    $requestForMissingAsset = CheckoutRequest::factory()->forAsset()->create(['requestable_id' => 99999999]);

    $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

    $this->assertNotSoftDeleted($this->validRequest);
    $this->assertDatabaseMissing('checkout_requests', ['requestable_id' => $requestForMissingAsset->requestable_id]);
});

test('clean old checkout requests command for soft deleted model', function () {
    $requestForSoftDeletedAssetModel = CheckoutRequest::factory()->forAssetModel()->create();
    Model::withoutEvents(fn () => $requestForSoftDeletedAssetModel->requestedItem->delete());

    $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

    $this->assertNotSoftDeleted($this->validRequest);
    $this->assertSoftDeleted($requestForSoftDeletedAssetModel->fresh());
});

test('clean old checkout requests command for missing model', function () {
    $requestForMissingModel = CheckoutRequest::factory()->forAssetModel()->create(['requestable_id' => 99999999]);

    $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

    $this->assertNotSoftDeleted($this->validRequest);
    $this->assertDatabaseMissing('checkout_requests', ['requestable_id' => $requestForMissingModel->requestable_id]);
});

test('clean old checkout requests command for soft deleted user', function () {
    $requestForSoftDeletedUser = CheckoutRequest::factory()->forAsset()->create();
    Model::withoutEvents(fn () => $requestForSoftDeletedUser->user->delete());

    $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

    $this->assertNotSoftDeleted($this->validRequest);
    $this->assertSoftDeleted($requestForSoftDeletedUser->fresh());
});

test('clean old checkout requests command for missing user', function () {
    $requestForMissingUser = CheckoutRequest::factory()->forAsset()->create(['user_id' => 99999999]);

    $this->artisan('snipeit:clean-old-checkout-requests')->assertExitCode(0);

    $this->assertNotSoftDeleted($this->validRequest);
    $this->assertDatabaseMissing('checkout_requests', ['user_id' => $requestForMissingUser->user_id]);
});
