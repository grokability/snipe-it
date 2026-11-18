<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutAccessories()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('checks out accessory to user by id', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $user = User::factory()->create();

    $content = handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
    ]);
});

test('checks out accessory to user by name', function () {
    $accessory = Accessory::factory()->create(['name' => 'Named Checkout Accessory', 'qty' => 5]);
    $user = User::factory()->create();

    $content = handle([
        'name' => 'Named Checkout Accessory',
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('checks out accessory to location', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $location = Location::factory()->create();

    $content = handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'location',
        'assigned_location' => $location->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $location->id,
        'assigned_type' => Location::class,
    ]);
});

test('checks out accessory to asset', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();

    $content = handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'asset',
        'assigned_asset' => $asset->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $asset->id,
        'assigned_type' => Asset::class,
    ]);
});

test('response includes checkout id for checkin', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $user = User::factory()->create();

    $content = handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->getStructuredContent();

    expect($content)->toHaveKey('checkout_id');
    expect($content['checkout_id'])->toBeInt();
});

test('fires checkout event', function () {
    Event::fake([CheckoutableCheckedOut::class]);

    $accessory = Accessory::factory()->create(['qty' => 5]);
    $user = User::factory()->create();

    handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]);

    Event::assertDispatched(CheckoutableCheckedOut::class, function (CheckoutableCheckedOut $event) use ($accessory) {
        return $event->checkoutable->id === $accessory->id;
    });
});

test('returns error when no units remaining', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->withoutItemsRemaining()->create();

    $response = handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when accessory not found', function () {
    expect(handle([
        'id' => 999999,
        'checkout_to_type' => 'user',
        'assigned_user' => User::factory()->create()->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when target user not found', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);

    expect(handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $user = User::factory()->create();

    expect(handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('refuses to check out archived accessory', function () {
    $accessory = Accessory::factory()->archived()->create(['qty' => 5]);
    $user = User::factory()->create();

    $response = handle([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
    ]);
});
