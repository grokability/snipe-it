<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;

/**
 * Regression tests for the CAND-09 race on acceptance finalization.
 *
 * CheckoutAcceptance::accept() and decline() used to do an unconditional
 * save() followed by the item-specific side effects (deleting an accessory
 * checkout, nulling an asset's assigned_to, etc). Two overlapping requests
 * could both load the acceptance while isPending() was true and both run
 * the side-effect chain. For accessory declines in particular,
 * Accessory::declinedCheckout picks the "latest" AccessoryCheckout row by
 * created_at with no acceptance binding, so the second stale request would
 * delete an older, legitimate assignment.
 *
 * The fix moves accept() and decline() to a compare-and-set UPDATE scoped
 * by whereNull on both timestamps. The first caller wins at the database
 * layer. The second caller's UPDATE affects zero rows, the method returns
 * false, and side effects do not run.
 */
test('second decline on a stale acceptance does not delete an unrelated older accessory checkout', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->create();

    $older = AccessoryCheckout::factory()->create([
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
        'created_at' => now()->subWeek(),
    ]);

    $newer = AccessoryCheckout::factory()->create([
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
        'created_at' => now(),
    ]);

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($accessory, 'checkoutable')
        ->create([
            'assigned_to_id' => $user->id,
            'qty' => 1,
        ]);

    $firstInstance = CheckoutAcceptance::find($acceptance->id);
    $secondInstance = CheckoutAcceptance::find($acceptance->id);

    expect($firstInstance->isPending())->toBeTrue();
    expect($secondInstance->isPending())->toBeTrue();

    expect($firstInstance->decline(''))->toBeTrue();

    expect($secondInstance->decline(''))->toBeFalse('Second decline on a stale acceptance instance must return false, not run side effects.');

    expect(AccessoryCheckout::find($newer->id))->toBeNull('Winning decline should have removed the newer AccessoryCheckout (the one tied to this acceptance).');
    expect(AccessoryCheckout::find($older->id))->not->toBeNull('Older unrelated AccessoryCheckout must survive. Stale second decline must not delete it.');
});

test('second accept on a stale acceptance returns false', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($asset, 'checkoutable')
        ->create(['assigned_to_id' => $user->id]);

    $firstInstance = CheckoutAcceptance::find($acceptance->id);
    $secondInstance = CheckoutAcceptance::find($acceptance->id);

    expect($firstInstance->accept('', null, null, 'first'))->toBeTrue();
    expect($secondInstance->accept('', null, null, 'second'))->toBeFalse('Second accept on a stale instance must return false.');

    expect(CheckoutAcceptance::find($acceptance->id)->note)->toBe('first', 'Winning accept note must survive. Stale second accept must not overwrite it.');
});

test('decline cannot overwrite a previously accepted acceptance', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($asset, 'checkoutable')
        ->create(['assigned_to_id' => $user->id]);

    $acceptInstance = CheckoutAcceptance::find($acceptance->id);
    $declineInstance = CheckoutAcceptance::find($acceptance->id);

    expect($acceptInstance->accept(''))->toBeTrue();
    expect($declineInstance->decline(''))->toBeFalse('Decline must not succeed after accept has already finalized the row.');

    $fresh = CheckoutAcceptance::find($acceptance->id);
    expect($fresh->accepted_at)->not->toBeNull();
    expect($fresh->declined_at)->toBeNull();
});

test('decline still runs qty side effects on the winning caller', function () {
    // qty > 1 case. The loop that used to live in the controller has
    // moved inside decline(), so the winning caller still deletes
    // $qty AccessoryCheckout rows.
    $user = User::factory()->create();
    $accessory = Accessory::factory()->create();

    AccessoryCheckout::factory()->count(3)->create([
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
    ]);

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($accessory, 'checkoutable')
        ->create([
            'assigned_to_id' => $user->id,
            'qty' => 3,
        ]);

    expect(AccessoryCheckout::where('assigned_to', $user->id)->count())->toBe(3);

    expect($acceptance->decline(''))->toBeTrue();

    expect(AccessoryCheckout::where('assigned_to', $user->id)->count())->toBe(0, 'qty=3 decline should remove all 3 AccessoryCheckout rows.');
});
