<?php

use App\Mail\CheckoutAccessoryMail;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses(\Tests\Support\MakesWatsonValidationLoud::class);

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.accessories.checkout', Accessory::factory()->create()))
        ->assertForbidden();
});

test('validation when checking out accessory', function () {
    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', Accessory::factory()->create()), [
            // missing assigned_user, assigned_location, assigned_asset
        ])
        ->assertStatusMessageIs('error');
});

test('accessory must be available when checking out', function () {
    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', Accessory::factory()->withoutItemsRemaining()->create()), [
            'assigned_user' => User::factory()->create()->id,
            'checkout_to_type' => 'user',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertJson(
            [
                'status' => 'error',
                'messages' => [
                    'checkout_qty' => [
                        trans_choice('admin/accessories/message.checkout.checkout_qty.lte', 0,
                            [
                                'number_currently_remaining' => 0,
                                'checkout_qty' => 1,
                                'number_remaining_after_checkout' => 0,
                            ]),
                    ],

                ],
                'payload' => null,
            ])
        ->assertStatus(200)
        ->json();
});

test('accessory can be checked out without qty', function () {
    $accessory = Accessory::factory()->create();
    $user = User::factory()->create();
    $admin = User::factory()->checkoutAccessories()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
            'checkout_to_type' => 'user',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->assertJson(['messages' => trans('admin/accessories/message.checkout.success')])
        ->json();

    expect($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0)->toBeTrue();

    expect(Actionlog::where([
        'action_type' => 'checkout',
        'target_id' => $user->id,
        'target_type' => User::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
        'created_by' => $admin->id,
    ])->count())->toEqual(1, 'Log entry either does not exist or there are more than expected');
    $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
});

test('accessory can be checked out with qty', function () {
    $accessory = Accessory::factory()->create(['qty' => 20]);
    $user = User::factory()->create();
    $admin = User::factory()->checkoutAccessories()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
            'checkout_to_type' => 'user',
            'checkout_qty' => 2,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->assertJson(['messages' => trans('admin/accessories/message.checkout.success')])
        ->json();

    expect($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0)->toBeTrue();

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'checkout',
        'target_id' => $user->id,
        'target_type' => User::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
        'quantity' => 2,
        'created_by' => $admin->id,
    ]);

    $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
});

test('accessory checkout infers user target when checkout to type omitted', function () {
    $accessory = Accessory::factory()->create();
    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', User::class)
        ->where('assigned_to', $user->id)
        ->count())->toBe(1);
});

test('accessory checkout infers asset target when checkout to type omitted', function () {
    $accessory = Accessory::factory()->create();
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_asset' => $asset->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Asset::class)
        ->where('assigned_to', $asset->id)
        ->count())->toBe(1);
});

test('accessory checkout infers location target when checkout to type omitted', function () {
    $accessory = Accessory::factory()->create();
    $location = Location::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_location' => $location->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Location::class)
        ->where('assigned_to', $location->id)
        ->count())->toBe(1);
});

test('accessory checkout rejects multiple target fields', function () {
    $accessory = Accessory::factory()->create();
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $response = $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
            'assigned_location' => $location->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    expect($response->json('messages'))->toHaveKey('assigned_user');
    expect($accessory->checkouts()->count())->toBe(0);
});

test('accessory cannot be checked out to invalid user', function () {
    $accessory = Accessory::factory()->create();
    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => 'invalid-user-id',
            'checkout_to_type' => 'user',
            'note' => 'oh hi there',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    expect($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0)->toBeFalse();
});

test('user sent notification upon checkout', function () {
    Mail::fake();

    $accessory = Accessory::factory()->requiringAcceptance()->create();
    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
            'checkout_to_type' => 'user',
        ]);

    Mail::assertSent(CheckoutAccessoryMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('action log created upon checkout', function () {
    $accessory = Accessory::factory()->create();
    $actor = User::factory()->checkoutAccessories()->create();
    $user = User::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
            'checkout_to_type' => 'user',
            'note' => 'oh hi there',
        ]);

    expect(Actionlog::where([
        'action_type' => 'checkout',
        'target_id' => $user->id,
        'target_type' => User::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
        'created_by' => $actor->id,
        'note' => 'oh hi there',
    ])->count())->toEqual(1, 'Log entry either does not exist or there are more than expected');
    $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
});

test('superuser cannot checkout accessory to a target in another company when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $superuser = User::factory()->superuser()->withoutCompany()->create();
    $accessoryInCompanyA = Accessory::factory()->for($companyA)->create(['qty' => 1]);
    $userInCompanyB = User::factory()->forCompany($companyB)->create();

    $this->actingAsForApi($superuser)
        ->postJson(route('api.accessories.checkout', $accessoryInCompanyA), [
            'assigned_user' => $userInCompanyB->id,
            'checkout_to_type' => 'user',
            'checkout_qty' => 1,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    $this->assertDatabaseMissing('accessories_checkout', [
        'accessory_id' => $accessoryInCompanyA->id,
        'assigned_to' => $userInCompanyB->id,
        'assigned_type' => User::class,
    ]);

    $this->assertDatabaseMissing('action_logs', [
        'created_by' => $superuser->id,
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $userInCompanyB->id,
        'item_type' => Accessory::class,
        'item_id' => $accessoryInCompanyA->id,
    ]);

    expect($accessoryInCompanyA->fresh()->numRemaining())->toEqual(1);
});

test('user in same company can checkout accessory when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->create(['qty' => 5]);
    $target = $company->users()->save(User::factory()->make());
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $target->id,
            'checkout_to_type' => 'user',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('user in multiple companies can checkout accessory from any of their companies when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $target = User::factory()->create();
    $target->companies()->sync([$companyA->id, $companyB->id]);

    $accessoryInA = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $accessoryInB = Accessory::factory()->for($companyB)->create(['qty' => 5]);
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessoryInA), [
            'assigned_user' => $target->id,
            'checkout_to_type' => 'user',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessoryInB), [
            'assigned_user' => $target->id,
            'checkout_to_type' => 'user',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('accessory can be checked out to location when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->create();
    $location = Location::factory()->create(['company_id' => $company->id]);
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_location' => $location->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Location::class)
        ->where('assigned_to', $location->id)
        ->count())->toBe(1);
});

test('accessory can be checked out to asset when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->create();
    $targetAsset = Asset::factory()->for($company)->create();
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_asset' => $targetAsset->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Asset::class)
        ->where('assigned_to', $targetAsset->id)
        ->count())->toBe(1);
});

test('accessory cannot be checked out to location in different company when fmcs scoped locations enabled', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessory = Accessory::factory()->for($companyA)->create();
    $locationInB = Location::factory()->create(['company_id' => $companyB->id]);
    $actor = User::factory()->superuser()->create();

    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_location' => $locationInB->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    expect($accessory->checkouts()->count())->toBe(0);
});

test('accessory cannot be checked out to asset in different company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessory = Accessory::factory()->for($companyA)->create();
    $assetInB = Asset::factory()->for($companyB)->create();
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_asset' => $assetInB->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    expect($accessory->checkouts()->count())->toBe(0);
});

test('accessory can be checked out to cross company location when fmcs location scoping is off', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessory = Accessory::factory()->for($companyA)->create();
    $locationInB = Location::factory()->create(['company_id' => $companyB->id]);
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_location' => $locationInB->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Location::class)
        ->where('assigned_to', $locationInB->id)
        ->count())->toBe(1);
});

test('accessory can be checked out to child company location under scoped fmcs', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->childOf($parent)->create();
    $accessory = Accessory::factory()->for($parent)->create();
    $locationInChild = Location::factory()->create(['company_id' => $child->id]);
    $actor = User::factory()->superuser()->create();

    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_location' => $locationInChild->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Location::class)
        ->where('assigned_to', $locationInChild->id)
        ->count())->toBe(1);
});

test('accessory can be checked out to null company location in floater mode', function () {
    $this->settings->enableFloaterMode();

    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->create();
    $locationWithNoCompany = Location::factory()->create(['company_id' => null]);
    $actor = User::factory()->superuser()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_location' => $locationWithNoCompany->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($accessory->checkouts()
        ->where('assigned_type', Location::class)
        ->where('assigned_to', $locationWithNoCompany->id)
        ->count())->toBe(1);
});

test('checkout refuses when inventory is already exhausted', function () {
    $target = User::factory()->create();
    $accessory = Accessory::factory()->create(['qty' => 1]);
    $actor = User::factory()->superuser()->create();

    \App\Models\AccessoryCheckout::create([
        'accessory_id' => $accessory->id,
        'assigned_to' => $target->id,
        'assigned_type' => User::class,
        'created_by' => $actor->id,
    ]);

    expect($accessory->fresh()->numRemaining())->toBe(0);

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $target->id,
            'checkout_to_type' => 'user',
            'checkout_qty' => 1,
        ])
        ->assertStatusMessageIs('error');

    expect($accessory->checkouts()->count())->toBe(1, 'A second pivot row would mean the register went negative');
    expect($accessory->fresh()->numRemaining())->toBe(0);
});
