<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('broken mail transport does not 500 the checkin', function () {
    // Category that triggers the mail path on checkin so the
    // listener actually reaches Mail::send during the request.
    // Without a mail-triggering category, the listener early-exits
    // and the TypeError never has a chance to fire.
    $category = Category::factory()->forAssets()->sendsCheckinEmail()->create();
    $assignee = User::factory()->create(['email' => 'assignee@example.test']);
    $asset = Asset::factory()
        ->for(\App\Models\AssetModel::factory()->for($category, 'category'), 'model')
        ->assignedToUser($assignee)
        ->create();

    // Simulate the transport-level TypeError that unresolved Docker
    // link vars like MAIL_PORT=${MAIL_PORT_587_TCP_PORT} produce.
    // Any Throwable raised inside the listener's Mail::send block
    // should be swallowed by the try/catch.
    Mail::shouldReceive('to')->andThrow(new \TypeError('simulated Dsn port TypeError'));

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset))
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->assignedTo)->toBeNull('Asset should be checked in despite the mail transport error.');
    expect($asset->assigned_type)->toBeNull();
});
