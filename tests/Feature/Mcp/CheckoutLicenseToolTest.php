<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
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

test('checks out license to user by id', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    $content = handle([
        'id' => $license->id,
        'assigned_to' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['assigned_to_type'])->toEqual('user');
    expect($content['assigned_to_id'])->toEqual($user->id);
    $this->assertDatabaseHas('license_seats', [
        'license_id' => $license->id,
        'assigned_to' => $user->id,
    ]);
});

test('checks out license to user by name', function () {
    $license = License::factory()->create(['name' => 'Named Checkout License', 'seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    $content = handle([
        'name' => 'Named Checkout License',
        'assigned_to' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('checks out license to asset', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $asset = Asset::factory()->create();

    $content = handle([
        'id' => $license->id,
        'asset_id' => $asset->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['assigned_to_type'])->toEqual('asset');
    expect($content['assigned_to_id'])->toEqual($asset->id);
    $this->assertDatabaseHas('license_seats', [
        'license_id' => $license->id,
        'asset_id' => $asset->id,
    ]);
});

test('response includes seat id for checkin', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    $content = handle([
        'id' => $license->id,
        'assigned_to' => $user->id,
    ])->getStructuredContent();

    expect($content)->toHaveKey('seat_id');
    expect($content['seat_id'])->toBeInt();
    expect(LicenseSeat::find($content['seat_id']))->not->toBeNull();
});

test('fires checkout event', function () {
    Event::fake([CheckoutableCheckedOut::class]);

    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    handle([
        'id' => $license->id,
        'assigned_to' => $user->id,
    ]);

    Event::assertDispatched(CheckoutableCheckedOut::class);
});

test('returns error when no seats remaining', function () {
    $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $seat = $license->freeSeat();
    $seat->assigned_to = $user1->id;
    $seat->save();

    $response = handle([
        'id' => $license->id,
        'assigned_to' => $user2->id,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when neither assignee provided', function () {
    $license = License::factory()->create(['seats' => 3]);

    expect(handle(['id' => $license->id])->responses()->first()->isError())->toBeTrue();
});

test('returns error when license not found', function () {
    expect(handle([
        'id' => 999999,
        'assigned_to' => User::factory()->create()->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not found', function () {
    $license = License::factory()->create(['seats' => 3]);

    expect(handle([
        'id' => $license->id,
        'assigned_to' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    expect(handle([
        'id' => $license->id,
        'assigned_to' => $user->id,
    ])->responses()->first()->isError())->toBeTrue();
});
