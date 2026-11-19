<?php

use App\Mail\CheckoutConsumableMail;
use App\Models\Actionlog;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('checking out consumable requires correct permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.consumables.checkout', Consumable::factory()->create()))
        ->assertForbidden();
});

test('validation when checking out consumable', function () {
    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', Consumable::factory()->create()), [
            // missing assigned_to
        ])
        ->assertStatusMessageIs('error');
});

test('consumable must be available when checking out', function () {
    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', Consumable::factory()->withoutItemsRemaining()->create()), [
            'assigned_to' => User::factory()->create()->id,
        ])
        ->assertStatusMessageIs('error');
});

test('consumable can be checked out', function () {
    $consumable = Consumable::factory()->create();
    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $user->id,
        ]);

    expect($user->consumables->contains($consumable))->toBeTrue();
    $this->assertHasTheseActionLogs($consumable, ['create', 'checkout']);
});

test('consumable can be checked out with quantity', function () {
    $consumable = Consumable::factory()->create();
    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $user->id,
            'checkout_qty' => 2,
        ]);

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Consumable::class,
        'item_id' => $consumable->id,
        'target_type' => User::class,
        'target_id' => $user->id,
        'action_type' => 'checkout',
        'quantity' => 2,
    ]);
});

test('user sent notification upon checkout', function () {
    Mail::fake();

    $consumable = Consumable::factory()->requiringAcceptance()->create();

    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $user->id,
        ]);

    Mail::assertSent(CheckoutConsumableMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('pivot row created by is the actor not the target', function () {
    // Regression: previously the pivot's created_by was set to $user->id
    // (the checkout target), so audit surfaces that read consumables_users
    // (e.g. "who checked this consumable out to me") would show the target
    // as their own creator. The action_logs stream separately recorded the
    // correct actor, which is why the pivot bug survived.
    $consumable = Consumable::factory()->create();
    $actor = User::factory()->checkoutConsumables()->create();
    $target = User::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $target->id,
            'note' => 'created_by attribution regression',
        ]);

    $this->assertDatabaseHas('consumables_users', [
        'consumable_id' => $consumable->id,
        'assigned_to' => $target->id,
        'created_by' => $actor->id,
    ]);

    $this->assertDatabaseMissing('consumables_users', [
        'consumable_id' => $consumable->id,
        'assigned_to' => $target->id,
        'created_by' => $target->id,
    ]);
});

test('action log created upon checkout', function () {
    $consumable = Consumable::factory()->create();
    $actor = User::factory()->checkoutConsumables()->create();
    $user = User::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $user->id,
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

test('superuser cannot checkout consumable to a user in another company when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $superuser = User::factory()->superuser()->withoutCompany()->create();
    $consumableInCompanyA = Consumable::factory()->for($companyA)->create(['qty' => 1]);
    $userInCompanyB = User::factory()->forCompany($companyB)->create();

    $this->actingAsForApi($superuser)
        ->postJson(route('api.consumables.checkout', $consumableInCompanyA), [
            'assigned_to' => $userInCompanyB->id,
            'checkout_qty' => 1,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    $this->assertDatabaseMissing('consumables_users', [
        'consumable_id' => $consumableInCompanyA->id,
        'assigned_to' => $userInCompanyB->id,
    ]);

    $this->assertDatabaseMissing('action_logs', [
        'created_by' => $superuser->id,
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $userInCompanyB->id,
        'item_type' => Consumable::class,
        'item_id' => $consumableInCompanyA->id,
    ]);

    expect($consumableInCompanyA->fresh()->numRemaining())->toEqual(1);
});

test('user in same company can checkout consumable when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $consumable = Consumable::factory()->for($company)->create(['qty' => 5]);
    $target = $company->users()->save(User::factory()->make());
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('user in multiple companies can checkout consumable from any of their companies when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $target = User::factory()->create();
    $target->companies()->sync([$companyA->id, $companyB->id]);

    $consumableInA = Consumable::factory()->for($companyA)->create(['qty' => 5]);
    $consumableInB = Consumable::factory()->for($companyB)->create(['qty' => 5]);
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.consumables.checkout', $consumableInA), [
            'assigned_to' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->actingAsForApi($actor)
        ->postJson(route('api.consumables.checkout', $consumableInB), [
            'assigned_to' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('checkout refuses when inventory is already exhausted', function () {
    $target = User::factory()->create();
    $consumable = Consumable::factory()->create(['qty' => 1]);

    // Drain the consumable via a direct pivot insert. Same state a
    // concurrent request would have left mid-transaction.
    $consumable->users()->attach($consumable->id, [
        'consumable_id' => $consumable->id,
        'assigned_to' => $target->id,
        'created_by' => User::factory()->superuser()->create()->id,
    ]);

    expect($consumable->fresh()->numRemaining())->toBe(0);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $target->id,
            'checkout_qty' => 1,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    // The pre-drained row is the only pivot; no second row got added.
    expect($consumable->users()->count())->toBe(1, 'A second pivot row would mean the register went negative');
    expect($consumable->fresh()->numRemaining())->toBe(0);
});
