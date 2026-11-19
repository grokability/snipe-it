<?php

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('licenses.checkout', License::factory()->create()->id))
        ->assertOk();
});

test('notes are stored in action log on checkout to asset', function () {
    $admin = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $licenseSeat = LicenseSeat::factory()->create();

    $this->actingAs($admin)
        ->post(route('licenses.checkout', $licenseSeat->license), [
            'checkout_to_type' => 'asset',
            'assigned_user' => null,
            'assigned_asset' => $asset->id,
            'notes' => 'oh hi there',
        ]);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'checkout',
        'target_id' => $asset->id,
        'target_type' => Asset::class,
        'item_id' => $licenseSeat->license->id,
        'item_type' => License::class,
        'note' => 'oh hi there',
    ]);
    $this->assertHasTheseActionLogs($licenseSeat->license, ['add seats', 'create', 'checkout']);
    // TODO - TOTALLY out-of-order
});

test('notes are stored in action log on checkout to user', function () {
    $admin = User::factory()->superuser()->create();
    $licenseSeat = LicenseSeat::factory()->create();

    $this->actingAs($admin)
        ->post(route('licenses.checkout', $licenseSeat->license), [
            'checkout_to_type' => 'user',
            'assigned_user' => $admin->id,
            'assigned_asset' => null,
            'notes' => 'oh hi there',
        ]);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'checkout',
        'target_id' => $admin->id,
        'target_type' => User::class,
        'item_id' => $licenseSeat->license->id,
        'item_type' => License::class,
        'note' => 'oh hi there',
    ]);
    $this->assertHasTheseActionLogs($licenseSeat->license, ['add seats', 'create', 'checkout']);
    // FIXME - out-of-order
});

test('license checkout page post is redirected if redirect selection is index', function () {
    $license = License::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('licenses.checkout', $license))
        ->post(route('licenses.checkout', $license), [
            'assigned_user' => User::factory()->create()->id,
            'redirect_option' => 'index',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('licenses.index'));
});

test('license checkout page post is redirected if redirect selection is item', function () {
    $license = License::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('licenses.checkout', $license))
        ->post(route('licenses.checkout', $license), [
            'assigned_user' => User::factory()->create()->id,
            'redirect_option' => 'item',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('licenses.show', $license));
});

test('license checkout page post is redirected if redirect selection is user target', function () {
    $user = User::factory()->create();
    $license = License::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('licenses.checkout', $license))
        ->post(route('licenses.checkout', $license), [
            'assigned_user' => $user->id,
            'redirect_option' => 'target',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('users.show', $user));
});

test('license checkout page post is redirected if redirect selection is asset target', function () {
    $asset = Asset::factory()->create();
    $license = License::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('licenses.checkout', $license))
        ->post(route('licenses.checkout', $license), [
            'assigned_asset' => $asset->id,
            'redirect_option' => 'target',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.show', $asset));
});

test('license checkout page post redirects to signature page when sign in place is checked', function () {
    $targetUser = User::factory()->create();
    $seat = LicenseSeat::factory()->requiringAcceptance()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->from(route('licenses.checkout', $seat->license))
        ->post(route('licenses.checkout', $seat->license), [
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'sign_in_place' => 1,
        ]);

    $acceptance = CheckoutAcceptance::query()
        ->where('checkoutable_type', LicenseSeat::class)
        ->where('assigned_to_id', $targetUser->id)
        ->pending()
        ->latest()
        ->first();

    expect($acceptance)->not->toBeNull();
    $this->assertDatabaseCount('checkout_acceptances', 1);

    $response->assertStatus(302)
        ->assertRedirect(route('account.accept.item', $acceptance));
});

test('license sign in place creates acceptance when acceptance not required', function () {
    $targetUser = User::factory()->create();
    $seat = LicenseSeat::factory()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->from(route('licenses.checkout', $seat->license))
        ->post(route('licenses.checkout', $seat->license), [
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'sign_in_place' => 1,
        ]);

    $acceptance = CheckoutAcceptance::query()
        ->where('checkoutable_type', LicenseSeat::class)
        ->where('assigned_to_id', $targetUser->id)
        ->pending()
        ->latest()
        ->first();

    expect($acceptance)->not->toBeNull();

    // The fallback does not set qty, so it stays null (the column default).
    expect($acceptance->qty)->toBeNull();
    $this->assertDatabaseCount('checkout_acceptances', 1);

    $response->assertStatus(302)
        ->assertRedirect(route('account.accept.item', $acceptance));
});

test('license checkout stores sign in place preference in session', function () {
    $targetUser = User::factory()->create();
    $seat = LicenseSeat::factory()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->post(route('licenses.checkout', $seat->license), [
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'sign_in_place' => 1,
        ]);

    $response->assertSessionHas('sign_in_place', true);
});

test('web checkout by explicit seat id rejects retired unreassignable seat', function () {
    $license = License::factory()->create(['seats' => 2, 'reassignable' => 0]);
    $seats = $license->licenseseats()->orderBy('id')->get();
    $retiredSeat = $seats[0];

    // unreassignable_seat is not fillable, so ->update() would silently
    // skip it. Direct property assignment + save persists it.
    $retiredSeat->unreassignable_seat = true;
    $retiredSeat->assigned_to = null;
    $retiredSeat->save();

    $target = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('licenses.checkout.save', ['licenseId' => $license->id, 'seatId' => $retiredSeat->id]), [
            'assigned_user' => $target->id,
            'redirect_option' => 'index',
        ])
        ->assertSessionHas('error');

    expect($retiredSeat->fresh()->assigned_to)->toBeNull();
    expect((bool) $retiredSeat->fresh()->unreassignable_seat)->toBeTrue();
});

test('web checkout by explicit seat id rejects seat already assigned to user', function () {
    $license = License::factory()->create(['seats' => 2]);
    $seats = $license->licenseseats()->orderBy('id')->get();

    $originalHolder = User::factory()->create();
    $seats[0]->update(['assigned_to' => $originalHolder->id]);

    $newTarget = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('licenses.checkout.save', ['licenseId' => $license->id, 'seatId' => $seats[0]->id]), [
            'assigned_user' => $newTarget->id,
            'redirect_option' => 'index',
        ])
        ->assertSessionHas('error');

    expect($seats[0]->fresh()->assigned_to)->toEqual($originalHolder->id, 'Web checkout must never silently displace the current holder.');
});
