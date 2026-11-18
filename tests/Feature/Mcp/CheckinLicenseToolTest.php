<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutLicenses()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

function checkoutToUser(License $license, User $user): LicenseSeat
{
    $response = handle(new Request([
        'id' => $license->id,
        'assigned_to' => $user->id,
    ]));

    $seatId = $response->getStructuredContent()['seat_id'];

    return LicenseSeat::find($seatId);
}

test('checks in seat by seat id', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();
    $seat = checkoutToUser($license, $user);

    $content = handle(['seat_id' => $seat->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('license_seats', [
        'id' => $seat->id,
        'assigned_to' => null,
        'asset_id' => null,
    ]);
});

test('response includes license info', function () {
    $license = License::factory()->create(['name' => 'Checkin License', 'seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();
    $seat = checkoutToUser($license, $user);

    $content = handle(['seat_id' => $seat->id])->getStructuredContent();

    expect($content['seat_id'])->toEqual($seat->id);
    expect($content['license_id'])->toEqual($license->id);
    expect($content['license_name'])->toEqual('Checkin License');
});

test('fires checkin event', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();
    $seat = checkoutToUser($license, $user);

    handle(['seat_id' => $seat->id]);

    Event::assertDispatched(CheckoutableCheckedIn::class);
});

test('returns error when seat not found', function () {
    expect(handle(['seat_id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when seat is not checked out', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $seat = $license->freeSeat();

    expect(handle(['seat_id' => $seat->id])->responses()->first()->isError())->toBeTrue();
});

test('sets unreassignable flag when license not reassignable', function () {
    $license = License::factory()->create(['seats' => 1, 'reassignable' => false]);
    $user = User::factory()->create();

    $seat = $license->freeSeat();
    $seat->assigned_to = $user->id;
    $seat->save();

    handle(['seat_id' => $seat->id]);

    $this->assertDatabaseHas('license_seats', [
        'id' => $seat->id,
        'unreassignable_seat' => true,
    ]);
});

test('does not set unreassignable flag when license is reassignable', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();
    $seat = checkoutToUser($license, $user);

    handle(['seat_id' => $seat->id]);

    $refreshed = LicenseSeat::find($seat->id);
    expect((bool) $refreshed->unreassignable_seat)->toBeFalse();
});

test('returns error when user lacks permission', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();
    $seat = checkoutToUser($license, $user);

    $this->actingAs(User::factory()->create());

    expect(handle(['seat_id' => $seat->id])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('license_seats', [
        'id' => $seat->id,
        'assigned_to' => $user->id,
    ]);
});
