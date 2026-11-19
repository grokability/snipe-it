<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

test('checking in asset requires correct permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.asset.checkin', Asset::factory()->assignedToUser()->create()))
        ->assertForbidden();
});

test('cannot check in non existent asset', function () {
    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', ['id' => 'does-not-exist']))
        ->assertStatusMessageIs('error');
});

test('cannot check in asset that is not checked out', function () {
    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', Asset::factory()->create()->id))
        ->assertStatusMessageIs('error');
});

test('asset can be checked in', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $location = Location::factory()->create();
    $status = Statuslabel::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create([
        'expected_checkin' => now()->addDay(),
        'last_checkin' => null,
        'accepted' => 'accepted',
    ]);

    expect($asset->assignedTo->is($user))->toBeTrue();

    $currentTimestamp = now();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), [
            'name' => 'Changed Name',
            'status_id' => $status->id,
            'location_id' => $location->id,
        ])
        ->assertOk();

    expect($asset->refresh()->assignedTo)->toBeNull();
    expect($asset->expected_checkin)->toBeNull();
    expect($asset->assignedTo)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
    expect($asset->accepted)->toBeNull();
    expect($asset->name)->toEqual('Changed Name');
    expect($asset->status_id)->toEqual($status->id);
    expect($asset->location()->is($location))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create'/* , 'checkout', 'checkin from' */]); // TODO - the Event::fake() is probably getting in the way here

    Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($currentTimestamp) {
        // this could be better mocked but is ok for now.
        return (int) Carbon::parse($event->action_date)->diffInSeconds($currentTimestamp, true) < 2;
    }, 1);
});

test('location is set to rtd location by default upon checkin', function () {
    $rtdLocation = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create([
        'location_id' => Location::factory()->create()->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset->id));

    expect($asset->refresh()->location()->is($rtdLocation))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create', /* 'checkout', */ 'checkin from']); // FIXME?
});

test('default location can be updated upon checkin', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), [
            'location_id' => $location->id,
            'update_default_location' => true,
        ]);

    expect($asset->refresh()->defaultLoc()->is($location))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create', /* 'checkout', */ 'checkin from']); // FIXME?
});

test('assets license seats are cleared upon checkin', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    LicenseSeat::factory()->assignedToUser()->for($asset)->create();

    expect($asset->licenseseats->first()->assigned_to)->not->toBeNull();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset));

    expect($asset->refresh()->licenseseats->first()->assigned_to)->toBeNull();
});

test('checking in asset updates location of assets assigned to it', function () {
    $originalLocation = Location::factory()->create();
    $checkedOutLocation = Location::factory()->create();

    $parentAsset = Asset::factory()->assignedToLocation($checkedOutLocation)->create([
        'location_id' => $checkedOutLocation->id,
        'rtd_location_id' => $originalLocation->id,
    ]);

    $childAsset = Asset::factory()->create([
        'assigned_to' => $parentAsset->id,
        'assigned_type' => Asset::class,
        'location_id' => $checkedOutLocation->id,
        'rtd_location_id' => $originalLocation->id,
    ]);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $parentAsset), [
            'location_id' => $originalLocation->id,
        ])
        ->assertOk();

    expect($parentAsset->fresh()->location_id)->toEqual($originalLocation->id);
    expect($childAsset->fresh()->location_id)->toEqual($originalLocation->id);
    expect($childAsset->fresh()->assigned_to)->toEqual($parentAsset->id);
    expect($childAsset->fresh()->assigned_type)->toEqual(Asset::class);
});

test('legacy location values set to zero are updated', function () {
    $asset = Asset::factory()->canBeInvalidUponCreation()->assignedToUser()->create([
        'rtd_location_id' => 0,
        'location_id' => 0,
    ]);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset));

    expect($asset->refresh()->rtd_location_id)->toBeNull();
    expect($asset->rtd_location_id)->toEqual($asset->location_id);
});

test('pending checkout acceptances are cleared upon checkin', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $acceptance = CheckoutAcceptance::factory()->for($asset, 'checkoutable')->pending()->create();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset));

    expect($acceptance->exists())->toBeFalse('Acceptance was not deleted');
});

test('checkin time and action log note can be set', function () {
    Event::fake();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', Asset::factory()->assignedToUser()->create()), [
            // time is appended to the provided date in controller
            'checkin_at' => '2023-01-02',
            'note' => 'hi there',
        ]);

    Event::assertDispatched(function (CheckoutableCheckedIn $event) {
        return Carbon::parse('2023-01-02')->isSameDay(Carbon::parse($event->action_date))
            && $event->note === 'hi there';
    }, 1);
});

test('asset name is cleared on checkin when clear name is set', function () {
    $asset = Asset::factory()->assignedToUser()->create(['name' => 'My Asset Name']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), ['clear_name' => '1'])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->name)->toBeNull();
});

test('asset name is not cleared on checkin when clear name is not set', function () {
    $asset = Asset::factory()->assignedToUser()->create(['name' => 'My Asset Name']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset))
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->name)->toEqual('My Asset Name');
});

test('status update on not checked out asset still applies', function () {
    // Issue #19899: operator in Quickscan Checkin scans a box of
    // returned assets to retire them. Scanning an asset that was
    // never checked out used to short-circuit, dropping the status
    // change the form carried. Now the status / location / name /
    // notes still apply when the asset was already checked in.
    Event::fake([CheckoutableCheckedIn::class]);

    $retired = Statuslabel::factory()->create();
    $asset = Asset::factory()->create(['status_id' => Statuslabel::factory()->readyToDeploy()->create()->id]);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), [
            'status_id' => $retired->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->status_id)->toBe($retired->id);

    // No real checkin happened, so CheckoutableCheckedIn must not
    // fire. That event drives expiration-alert cascades, notification
    // mail, and action-log 'checkin from' writes, none of which make
    // sense when there was never a checkout.
    Event::assertNotDispatched(CheckoutableCheckedIn::class);
});

test('location update on not checked out asset still applies', function () {
    $newLocation = Location::factory()->create();
    $originalLocation = Location::factory()->create();
    $asset = Asset::factory()->create([
        'location_id' => $originalLocation->id,
        'rtd_location_id' => $originalLocation->id,
    ]);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), [
            'location_id' => $newLocation->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->location_id)->toBe($newLocation->id);
});

test('name update on not checked out asset still applies', function () {
    $asset = Asset::factory()->create(['name' => 'Old Name']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), [
            'name' => 'New Name',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->name)->toBe('New Name');
});

test('empty body on not checked out asset still errors', function () {
    // Preserves the pre-19899 behavior for the no-op case: if the
    // operator scanned an already-checked-in asset with no form
    // fields set, we still want to surface the "already checked in"
    // error rather than silently save-with-no-changes.
    $asset = Asset::factory()->create(['name' => 'Untouched']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset))
        ->assertStatusMessageIs('error');

    expect($asset->refresh()->name)->toBe('Untouched');
});

test('location is not reset to rtd when only status changes on not checked out asset', function () {
    // Operator changing a not-checked-out asset's status should not
    // trigger the "reset location to RTD" behavior that only makes
    // sense for a real checkin. Current location stays put unless
    // the operator supplies location_id explicitly.
    $rtd = Location::factory()->create();
    $current = Location::factory()->create();
    $retired = Statuslabel::factory()->create();
    $asset = Asset::factory()->create([
        'rtd_location_id' => $rtd->id,
        'location_id' => $current->id,
    ]);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkin', $asset), [
            'status_id' => $retired->id,
        ])
        ->assertOk();

    expect($asset->refresh()->location_id)->toBe($current->id);
});
