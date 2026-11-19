<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;

/**
 * Regression coverage for the concurrent-checkout race reported on
 * 2026-08-02. Assets\AssetCheckoutController::store() checked
 * availableForCheckout outside its checkOut() call, with no lock and no
 * re-check. Two racing form submits could both see the asset as available,
 * both invoke checkOut(), and land duplicate checkout-history rows plus a
 * doubled checkout_counter.
 *
 * The fix wraps checkOut() in a DB::transaction that begins with a
 * lockForUpdate re-fetch + re-check, mirroring the API fix and the
 * ConsumablesController pattern from GHSA-x4g2-87xc-m5jm. This test cannot
 * simulate two truly concurrent form submits in phpunit, but it pins the
 * behavioral consequence: an asset that has already been assigned
 * mid-flight cannot be checked out a second time.
 */
beforeEach(function () {
    Event::fake([CheckoutableCheckedOut::class]);
});

test('second checkout of already assigned asset does not increment counter', function () {
    $firstTarget = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($firstTarget)->create(['checkout_counter' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertRedirect();

    $asset->refresh();
    expect((int) $asset->checkout_counter)->toBe(1, 'checkout_counter must not increment when checkout is refused');
    expect((int) $asset->assigned_to)->toBe($firstTarget->id, 'existing assignment must remain intact');
});

test('second checkout of already assigned asset does not fire checkout event', function () {
    $firstTarget = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($firstTarget)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertRedirect();

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});
