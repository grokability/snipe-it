<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkinAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('checks in asset by asset tag', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => null,
        'assigned_type' => null,
        'accepted' => null,
    ]);
});

test('checks in asset by numeric id', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $content = handle(['id' => $asset->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('returns error when asset tag not found', function () {
    expect(handle(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset not checked out', function () {
    $asset = Asset::factory()->create();

    expect(handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError())->toBeTrue();
});

test('asset location resets to rtd location on checkin', function () {
    $rtdLocation = Location::factory()->create();
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create([
        'rtd_location_id' => $rtdLocation->id,
    ]);

    handle(['asset_tag' => $asset->asset_tag]);

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'location_id' => $rtdLocation->id,
    ]);
});

test('clears expected checkin date on checkin', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create([
        'expected_checkin' => now()->addDays(7),
    ]);

    handle(['asset_tag' => $asset->asset_tag]);

    expect($asset->fresh()->expected_checkin)->toBeNull();
});

test('fires checkin event', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    handle(['asset_tag' => $asset->asset_tag]);

    Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($asset) {
        return $event->checkoutable->id === $asset->id;
    });
});

test('response includes asset tag and location', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $content = handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

    expect($content['asset_tag'])->toEqual($asset->asset_tag);
    expect($content)->toHaveKey('model');
    expect($content)->toHaveKey('location');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    expect(handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $user->id]);
});
