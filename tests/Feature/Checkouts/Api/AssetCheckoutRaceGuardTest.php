<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;

/**
 * Regression coverage for the concurrent-checkout race reported on
 * 2026-08-02. Api\AssetsController::checkout() checked availableForCheckout
 * outside its transaction, then called Asset::checkOut() inside the
 * transaction with no row lock and no re-check. Two racing requests could
 * both see the asset as available, both invoke checkOut(), and land
 * duplicate checkout-history rows plus a doubled checkout_counter.
 *
 * The fix moves the availability re-check inside the transaction with
 * lockForUpdate on the asset row, mirroring the ConsumablesController
 * pattern from GHSA-x4g2-87xc-m5jm. This test cannot simulate two truly
 * concurrent HTTP requests in phpunit, but it pins the behavioral
 * consequence: an asset that has already been assigned mid-flight cannot
 * be checked out a second time. Any refactor that removes the lock or the
 * availability re-check would need to preserve this observable behavior.
 */
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
