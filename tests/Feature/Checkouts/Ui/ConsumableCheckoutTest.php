<?php

use App\Mail\CheckoutConsumableMail;
use App\Models\Actionlog;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('checking out consumable requires correct permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('consumables.checkout.store', Consumable::factory()->create()))
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.checkout.show', Consumable::factory()->create()->id))
        ->assertOk();
});

test('validation when checking out consumable', function () {
    $this->actingAs(User::factory()->checkoutConsumables()->create())
        ->post(route('consumables.checkout.store', Consumable::factory()->create()), [
            // missing assigned_user
        ])
        ->assertSessionHas('error');
});

test('consumable must be available when checking out', function () {
    $this->actingAs(User::factory()->checkoutConsumables()->create())
        ->post(route('consumables.checkout.store', Consumable::factory()->withoutItemsRemaining()->create()), [
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertSessionHas('error');
});

test('consumable can be checked out', function () {
    $consumable = Consumable::factory()->create();
    $user = User::factory()->create();

    $this->actingAs(User::factory()->checkoutConsumables()->create())
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $user->id,
        ]);

    expect($user->consumables->contains($consumable))->toBeTrue();
    $this->assertHasTheseActionLogs($consumable, ['create', 'checkout']);
});

test('user sent notification upon checkout', function () {
    Mail::fake();

    $consumable = Consumable::factory()->create();
    $user = User::factory()->create();

    $this->actingAs(User::factory()->checkoutConsumables()->create())
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $user->id,
        ]);

    Mail::assertSent(CheckoutConsumableMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('action log created upon checkout', function () {
    $consumable = Consumable::factory()->create();
    $actor = User::factory()->checkoutConsumables()->create();
    $user = User::factory()->create();

    $this->actingAs($actor)
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $user->id,
            'note' => 'oh hi there',
        ]);

    expect(Actionlog::where([
        'action_type' => 'checkout',
        'target_id' => $user->id,
        'target_type' => User::class,
        'item_id' => $consumable->id,
        'item_type' => Consumable::class,
        'created_by' => $actor->id,
        'note' => 'oh hi there',
    ])->count())->toEqual(1, 'Log entry either does not exist or there are more than expected');
});

test('consumable checkout page post is redirected if redirect selection is index', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('consumables.index'))
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => User::factory()->create()->id,
            'redirect_option' => 'index',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('consumables.index'));
});

test('consumable checkout page post is redirected if redirect selection is item', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('consumables.index'))
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => User::factory()->create()->id,
            'redirect_option' => 'item',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('consumables.show', $consumable));
});

test('consumable checkout page post is redirected if redirect selection is target', function () {
    $user = User::factory()->create();
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('components.index'))
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $user->id,
            'redirect_option' => 'target',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('users.show', $user));
});

test('quantity stored in action log', function () {
    $consumable = Consumable::factory()->create(['qty' => 3]);
    $user = User::factory()->create();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('components.index'))
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $user->id,
            'redirect_option' => 'target',
            'checkout_qty' => 2,
        ]);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'checkout',
        'target_id' => $user->id,
        'target_type' => User::class,
        'item_id' => $consumable->id,
        'item_type' => Consumable::class,
        'quantity' => 2,
        'created_by' => $admin->id,
    ]);
});

test('consumable checkout page post redirects to signature page when sign in place is checked', function () {
    $targetUser = User::factory()->create();
    $consumable = Consumable::factory()->requiringAcceptance()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->from(route('consumables.checkout.show', $consumable))
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'checkout_qty' => 2,
            'sign_in_place' => 1,
        ]);

    $acceptance = CheckoutAcceptance::query()
        ->where('checkoutable_type', Consumable::class)
        ->where('checkoutable_id', $consumable->id)
        ->where('assigned_to_id', $targetUser->id)
        ->pending()
        ->latest()
        ->first();

    expect($acceptance)->not->toBeNull();
    expect($acceptance->qty)->toEqual(2);
    $this->assertDatabaseCount('checkout_acceptances', 1);

    $response->assertStatus(302)
        ->assertRedirect(route('account.accept.item', $acceptance));
});

test('consumable sign in place creates acceptance when acceptance not required', function () {
    $targetUser = User::factory()->create();
    $consumable = Consumable::factory()->notRequiringAcceptance()->create(['qty' => 5]);

    $response = $this->actingAs(User::factory()->admin()->create())
        ->from(route('consumables.checkout.show', $consumable))
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'checkout_qty' => 2,
            'sign_in_place' => 1,
        ]);

    $acceptance = CheckoutAcceptance::query()
        ->where('checkoutable_type', Consumable::class)
        ->where('checkoutable_id', $consumable->id)
        ->where('assigned_to_id', $targetUser->id)
        ->pending()
        ->latest()
        ->first();

    expect($acceptance)->not->toBeNull();
    expect($acceptance->qty)->toEqual(2);
    $this->assertDatabaseCount('checkout_acceptances', 1);

    $response->assertStatus(302)
        ->assertRedirect(route('account.accept.item', $acceptance));
});

test('consumable checkout stores sign in place preference in session', function () {
    $targetUser = User::factory()->create();
    $consumable = Consumable::factory()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'checkout_qty' => 1,
            'sign_in_place' => 1,
        ]);

    $response->assertSessionHas('sign_in_place', true);
});

test('cannot check out more than physically remain when units are held outside viewers company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $userInCompanyA = User::factory()->forCompany($companyA)->create();
    $userInCompanyB = User::factory()->forCompany($companyB)->create();

    $consumable = Consumable::factory()
        ->for($companyA)
        // this can happen if FMCS was toggled on after checkouts
        ->checkedOutToUser($userInCompanyB)
        ->checkedOutToUser($userInCompanyB)
        ->create(['qty' => 2]);

    $this->assertDatabaseCount('consumables_users', 2);

    $this->actingAs(User::factory()->checkoutConsumables()->forCompany($companyA)->create())
        ->post(route('consumables.checkout.store', $consumable), [
            'assigned_user' => $userInCompanyA->id,
        ]);

    $this->assertDatabaseCount('consumables_users', 2);
});
