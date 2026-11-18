<?php

use App\Enums\CheckoutRequestState;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('requires asset checkout permission', function () {
    // Model requests fulfill via asset checkout, so the gate
    // is checkoutAssets (not a nonexistent models.checkout).
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('models.fulfill-requests.create', $model))
        ->assertForbidden();
});

test('fulfills ticked rows with distinct assets', function () {
    // Happy path: two rows tick with different asset ids from
    // the pool; both check out cleanly to their requesters.
    $admin = User::factory()->checkoutAssets()->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->rtd()->create();
    $assetA = Asset::factory()->create(['model_id' => $model->id, 'status_id' => $status->id]);
    $assetB = Asset::factory()->create(['model_id' => $model->id, 'status_id' => $status->id]);
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $aliceReq = CheckoutRequest::factory()->create([
        'user_id' => $alice->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);
    $bobReq = CheckoutRequest::factory()->create([
        'user_id' => $bob->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);

    $this->actingAs($admin)
        ->post(route('models.fulfill-requests.store', $model), [
            'enabled_requests' => [$aliceReq->id => '1', $bobReq->id => '1'],
            'asset_id' => [
                $aliceReq->id => $assetA->id,
                $bobReq->id => $assetB->id,
            ],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    expect($aliceReq->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($bobReq->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
    expect((int) $assetA->fresh()->assigned_to)->toBe($alice->id);
    expect((int) $assetB->fresh()->assigned_to)->toBe($bob->id);
});

test('double assignment of same asset wins first row only', function () {
    // Two rows accidentally point at the SAME asset id
    // (admin misclicked auto-pick, or hand-crafted POST, or
    // JS drift). Sequential per-row lockForUpdate + assigned_to
    // check means row 1 checks out, row 2 sees the asset now
    // taken and skips with row_asset_taken. Second requester's
    // request stays pending on the queue.
    $admin = User::factory()->checkoutAssets()->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->rtd()->create();
    $asset = Asset::factory()->create(['model_id' => $model->id, 'status_id' => $status->id]);
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $aliceReq = CheckoutRequest::factory()->create([
        'user_id' => $alice->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);
    $bobReq = CheckoutRequest::factory()->create([
        'user_id' => $bob->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);

    $this->actingAs($admin)
        ->post(route('models.fulfill-requests.store', $model), [
            'enabled_requests' => [$aliceReq->id => '1', $bobReq->id => '1'],
            'asset_id' => [
                $aliceReq->id => $asset->id,
                $bobReq->id => $asset->id,
            ],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    // First row wins the asset.
    expect($aliceReq->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
    expect((int) $asset->fresh()->assigned_to)->toBe($alice->id);

    // Second row's request stays pending - the admin can pick
    // a different asset (or wait for stock) on a later pass.
    expect($bobReq->fresh()->state)->toBe(CheckoutRequestState::Pending);
});

test('cross company fulfillment is rejected under fmcs', function () {
    // Regression for GHSA (reporter: superbot-helper): the model
    // bulk-fulfill route was calling checkOut() without the
    // canCheckoutTo() guard the sibling bulk-fulfill controllers
    // and the single-asset path all use, so a company-A asset
    // could be assigned to a requester who only belongs to
    // company B.
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->rtd()->create();
    $companyAAsset = Asset::factory()
        ->for($companyA, 'company')
        ->create(['model_id' => $model->id, 'status_id' => $status->id]);
    $companyBRequester = User::factory()->forCompany($companyB)->create();
    $companyBRequester->companies()->sync([$companyB->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $admin = User::factory()->superuser()->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $companyBRequester->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);

    $this->actingAs($admin)
        ->post(route('models.fulfill-requests.store', $model), [
            'enabled_requests' => [$request->id => '1'],
            'asset_id' => [$request->id => $companyAAsset->id],
            'notes' => [],
        ])
        ->assertRedirect();

    expect($request->fresh()->state)->toBe(CheckoutRequestState::Pending);
    expect($companyAAsset->fresh()->assigned_to)->toBeNull();
});

test('asset from a different model is rejected', function () {
    // Guard against hand-crafted POSTs pointing at an asset
    // that isn't of this model (would silently check out the
    // wrong physical unit).
    $admin = User::factory()->checkoutAssets()->create();
    $model = AssetModel::factory()->create();
    $otherModel = AssetModel::factory()->create();
    $status = Statuslabel::factory()->rtd()->create();
    $offModelAsset = Asset::factory()->create(['model_id' => $otherModel->id, 'status_id' => $status->id]);
    $requester = User::factory()->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);

    $this->actingAs($admin)
        ->post(route('models.fulfill-requests.store', $model), [
            'enabled_requests' => [$request->id => '1'],
            'asset_id' => [$request->id => $offModelAsset->id],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    expect($request->fresh()->state)->toBe(CheckoutRequestState::Pending);
    expect($offModelAsset->fresh()->assigned_to)->toBeNull();
});
