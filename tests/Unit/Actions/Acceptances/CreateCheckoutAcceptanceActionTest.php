<?php

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Models\Asset;
use App\Models\User;

test('it creates a pending acceptance associated with the item and user', function () {
    $asset = Asset::factory()->create();
    $assignedUser = User::factory()->create();

    $acceptance = CreateCheckoutAcceptanceAction::run($asset, $assignedUser);

    expect($acceptance->exists)->toBeTrue();
    expect($acceptance->isPending())->toBeTrue();
    expect($acceptance->checkoutable->is($asset))->toBeTrue();
    expect($acceptance->assignedTo->is($assignedUser))->toBeTrue();
});

test('it leaves qty and alert null when not provided', function () {
    $acceptance = CreateCheckoutAcceptanceAction::run(Asset::factory()->create(), User::factory()->create());

    expect($acceptance->qty)->toBeNull();
    expect($acceptance->alert_on_response_id)->toBeNull();
});

test('it stores the explicit qty and alert when provided', function () {
    $asset = Asset::factory()->create();
    $assignedUser = User::factory()->create();
    $alertUser = User::factory()->create();

    $acceptance = CreateCheckoutAcceptanceAction::run($asset, $assignedUser, qty: 3, alertOnResponseId: $alertUser->id);

    expect($acceptance->qty)->toEqual(3);
    expect($acceptance->alert_on_response_id)->toEqual($alertUser->id);
});
