<?php

use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\User;

test('returns only requestable models', function () {
    $requestable = AssetModel::factory()->create(['requestable' => 1]);
    $notRequestable = AssetModel::factory()->create(['requestable' => 0]);

    $rows = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
        ->getJson(route('api.assetmodels.requestable'))
        ->assertOk()
        ->json('rows');

    $ids = collect($rows)->pluck('id')->all();
    expect($ids)->toContain($requestable->id);
    expect($ids)->not->toContain($notRequestable->id);
});

test('row carries assigned to self true when user has open request', function () {
    $user = User::factory()->viewRequestableAssets()->create();
    $model = AssetModel::factory()->create(['requestable' => 1]);
    CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);

    $row = $this->actingAsForApi($user)
        ->getJson(route('api.assetmodels.requestable'))
        ->assertOk()
        ->json('rows.0');

    expect($row['assigned_to_self'])->toBeTrue();

    // Button-swap contract: caller with an open request sees
    // cancel, not request.
    expect($row['available_actions']['request'])->toBeFalse();
    expect($row['available_actions']['cancel'])->toBeTrue();
});

test('row carries assigned to self false for other users requests', function () {
    $me = User::factory()->viewRequestableAssets()->create();
    $someoneElse = User::factory()->create();
    $model = AssetModel::factory()->create(['requestable' => 1]);

    // Not my request.
    CheckoutRequest::factory()->create([
        'user_id' => $someoneElse->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
    ]);

    $row = $this->actingAsForApi($me)
        ->getJson(route('api.assetmodels.requestable'))
        ->assertOk()
        ->json('rows.0');

    expect($row['assigned_to_self'])->toBeFalse();
    expect($row['available_actions']['request'])->toBeTrue();
    expect($row['available_actions']['cancel'])->toBeFalse();
});

test('canceled requests do not flip assigned to self', function () {
    // Cancel semantics: a canceled row shouldn't leave the user
    // stuck seeing a cancel button they can't act on.
    $user = User::factory()->viewRequestableAssets()->create();
    $model = AssetModel::factory()->create(['requestable' => 1]);
    CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $model->id,
        'requestable_type' => AssetModel::class,
        'canceled_at' => now()->subMinute(),
    ]);

    $row = $this->actingAsForApi($user)
        ->getJson(route('api.assetmodels.requestable'))
        ->assertOk()
        ->json('rows.0');

    expect($row['assigned_to_self'])->toBeFalse();
    expect($row['available_actions']['request'])->toBeTrue();
});

test('cross type open requests do not flip assigned to self', function () {
    // Guard: an open request against an Accessory with the same
    // requestable_id must not bleed into the AssetModel row's
    // assigned_to_self flag. The transformer's contains-check
    // matches purely on user_id + canceled_at because the
    // eager-loaded requests collection is already scoped to this
    // model - but codify the isolation with a test so a future
    // refactor doesn't regress it.
    $user = User::factory()->viewRequestableAssets()->create();
    AssetModel::factory()->create(['requestable' => 1]);
    $accessory = Accessory::factory()->create();
    CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $row = $this->actingAsForApi($user)
        ->getJson(route('api.assetmodels.requestable'))
        ->assertOk()
        ->json('rows.0');

    expect($row['assigned_to_self'])->toBeFalse();
});

test('available actions view reflects caller permission', function () {
    // The formatter uses available_actions.view to decide
    // whether to render the name as an <a> link or plain text.
    // A user with the view-assetmodel permission gets view=true;
    // a user without it gets view=false.
    AssetModel::factory()->create(['requestable' => 1]);

    $withView = User::factory()->viewRequestableAssets()->viewAssetModels()->create();
    $withoutView = User::factory()->viewRequestableAssets()->create();

    $rowWith = $this->actingAsForApi($withView)
        ->getJson(route('api.assetmodels.requestable'))
        ->json('rows.0');
    $rowWithout = $this->actingAsForApi($withoutView)
        ->getJson(route('api.assetmodels.requestable'))
        ->json('rows.0');

    expect($rowWith['available_actions']['view'])->toBeTrue();
    expect($rowWithout['available_actions']['view'])->toBeFalse();
});

test('remaining reflects deployable asset count', function () {
    $model = AssetModel::factory()->create(['requestable' => 1]);
    $rtd = \App\Models\Statuslabel::factory()->rtd()->create();

    // Three deployable + unassigned assets of this model, one already
    // assigned. `remaining` should count only the three unassigned.
    \App\Models\Asset::factory()->count(3)->create([
        'model_id' => $model->id,
        'status_id' => $rtd->id,
        'assigned_to' => null,
    ]);
    \App\Models\Asset::factory()->create([
        'model_id' => $model->id,
        'status_id' => $rtd->id,
        'assigned_to' => User::factory()->create()->id,
        'assigned_type' => User::class,
    ]);

    $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
        ->getJson(route('api.assetmodels.requestable'))
        ->assertOk()
        ->json('rows.0');

    expect($row['remaining'])->toBe(3);
});
