<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('second checkin on an already checked in asset does not fire event or log', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $assignedUser = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignedUser)->create();

    $actor = User::factory()->checkinAssets()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkin', $asset))
        ->assertStatusMessageIs('success');

    Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
    $logsAfterFirstCheckin = Actionlog::query()
        ->where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->count();

    // Second request against the now-checked-in asset must bail out
    // on the re-checked null target. Pre-fix the unlocked initial read
    // could still observe the asset as checked out mid-race and emit
    // a duplicate event + a duplicate log row.
    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkin', $asset))
        ->assertStatusMessageIs('error');

    Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);

    $logsAfterSecondCheckin = Actionlog::query()
        ->where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->count();

    expect($logsAfterSecondCheckin)->toBe($logsAfterFirstCheckin, 'A second checkin against an already-checked-in asset must not create another action-log row.');
});
