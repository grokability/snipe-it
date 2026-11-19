<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([CheckoutableCheckedOut::class]);
});

test('second checkout of already assigned asset does not increment counter', function () {
    $firstTarget = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($firstTarget)->create(['checkout_counter' => 1]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertStatusMessageIs('error');

    $asset->refresh();
    expect((int) $asset->checkout_counter)->toBe(1, 'checkout_counter must not increment when checkout is refused');
    expect((int) $asset->assigned_to)->toBe($firstTarget->id, 'existing assignment must remain intact');
});

test('second checkout of already assigned asset does not fire checkout event', function () {
    $firstTarget = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($firstTarget)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertStatusMessageIs('error');

    // A racing second checkout that slipped past both the outer and the
    // locked re-check would fire CheckoutableCheckedOut and generate a
    // history row + counter bump via the CheckoutableListener chain.
    // Asserting the event never fires is a proxy for asserting no
    // downstream side-effects occurred.
    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});
