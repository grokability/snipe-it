<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedNotification;
use App\Notifications\AcceptanceItemDeclinedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

test('can accept accessory checkout', function () {
    $assignee = User::factory()->create();
    $accessory = Accessory::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($assignee, 'assignedTo')
        ->for($accessory, 'checkoutable')
        ->create(['qty' => 2]);

    $this->actingAs($assignee)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'A note here',
        ])
        ->assertRedirect();

    expect($checkoutAcceptance->refresh()->accepted_at)->not->toBeNull();
    expect($checkoutAcceptance->note)->toEqual('A note here');

    $assignee->accessories->contains($accessory);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'accepted',
        'target_id' => $assignee->id,
        'target_type' => User::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
        'quantity' => 2,
    ]);
});

test('can decline accessory checkout', function () {
    $assignee = User::factory()->create();
    $accessory = Accessory::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($assignee, 'assignedTo')
        ->for($accessory, 'checkoutable')
        ->create(['qty' => 2]);

    $this->actingAs($assignee)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'declined',
            'note' => 'A note here',
        ])
        ->assertRedirect();

    expect($checkoutAcceptance->refresh()->declined_at)->not->toBeNull();
    expect($checkoutAcceptance->note)->toEqual('A note here');

    $assignee->accessories->doesntContain($accessory);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'declined',
        'target_id' => $assignee->id,
        'target_type' => User::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
        'quantity' => 2,
    ]);
});

test('users name is included in accessory accepted notification', function () {
    Notification::fake();

    $this->settings->enableAdminCC();

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Accessory::factory()->appleMouse(), 'checkoutable')
        ->create();

    $this->actingAs($acceptance->assignedTo)
        ->post(route('account.store-acceptance', $acceptance), ['asset_acceptance' => 'accepted'])
        ->assertSessionHasNoErrors();

    expect($acceptance->fresh()->accepted_at)->not->toBeNull();

    Notification::assertSentTo(
        $acceptance,
        function (AcceptanceItemAcceptedNotification $notification) use ($acceptance) {
            $this->assertStringContainsString(
                $acceptance->assignedTo->present()->fullName,
                $notification->toMail()->render()
            );

            return true;
        }
    );
});

test('users name is included in accessory declined notification', function () {
    Notification::fake();

    $this->settings->enableAdminCC();

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Accessory::factory()->appleMouse(), 'checkoutable')
        ->create();

    $this->actingAs($acceptance->assignedTo)
        ->post(route('account.store-acceptance', $acceptance), ['asset_acceptance' => 'declined'])
        ->assertSessionHasNoErrors();

    expect($acceptance->fresh()->declined_at)->not->toBeNull();

    Notification::assertSentTo(
        $acceptance,
        function (AcceptanceItemDeclinedNotification $notification) use ($acceptance) {
            $this->assertStringContainsString(
                $acceptance->assignedTo->present()->fullName,
                $notification->toMail($acceptance)->render()
            );

            return true;
        }
    );
});

test('user is not able to accept an asset assigned to a different user', function () {
    Notification::fake();

    $otherUser = User::factory()->create();

    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Asset::factory()->laptopMbp(), 'checkoutable')
        ->create();

    $this->actingAs($otherUser)
        ->post(route('account.store-acceptance', $acceptance), ['asset_acceptance' => 'accepted'])
        ->assertSessionHas(['error' => trans('admin/users/message.error.incorrect_user_accepted')]);

    expect($acceptance->fresh()->accepted_at)->toBeNull();
});

test('all accessory checkout entries are removed when user declines acceptance', function () {
    $assignee = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAccessories()->create());

    // create accessory that requires acceptance
    $accessory = Accessory::factory()->requiringAcceptance()->create(['qty' => 5]);

    // checkout 3 accessories to the user
    $this->post(route('accessories.checkout.store', $accessory), [
        'assigned_user' => $assignee->id,
        'checkout_qty' => 3,
    ]);

    $originalAccessoryCheckoutCount = AccessoryCheckout::count();

    // find the acceptance to be declined
    $checkoutAcceptance = CheckoutAcceptance::query()
        ->where([
            'assigned_to_id' => $assignee->id,
            'qty' => 3,
        ])
        ->whereNull('accepted_at')
        ->whereNull('declined_at')
        ->whereHasMorph(
            'checkoutable',
            [Accessory::class],
        )
        ->sole();

    // decline the checkout
    $this->actingAs($assignee)
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'declined',
        ]);

    expect(AccessoryCheckout::count())->toEqual($originalAccessoryCheckoutCount - 3);
});

test('admin can complete sign in place accessory acceptance and is redirected to selected destination', function () {
    $assignee = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $accessory = Accessory::factory()->create();

    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($assignee, 'assignedTo')
        ->for($accessory, 'checkoutable')
        ->create();

    $this->actingAs($admin)
        ->withSession([
            'sign_in_place_acceptance_id' => $checkoutAcceptance->id,
            'sign_in_place_item_id' => $accessory->id,
            'sign_in_place_resource_type' => 'Accessories',
            'redirect_option' => 'target',
            'checkout_to_type' => 'user',
        ])
        ->post(route('account.store-acceptance', $checkoutAcceptance), [
            'asset_acceptance' => 'accepted',
            'note' => 'signed in person',
        ])
        ->assertRedirect(route('users.show', $assignee));

    expect($checkoutAcceptance->refresh()->accepted_at)->not->toBeNull();
});
