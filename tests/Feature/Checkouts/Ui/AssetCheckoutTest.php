<?php

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([CheckoutableCheckedOut::class]);
});

test('checking out asset requires correct permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('hardware.checkout.store', Asset::factory()->create()), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertForbidden();
});

test('non existent asset cannot be checked out', function () {
    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', 1000), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
            'name' => 'Changed Name',
        ])
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.index'));

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('asset not available for checkout cannot be checked out', function () {
    $assetAlreadyCheckedOut = Asset::factory()->assignedToUser()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $assetAlreadyCheckedOut), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.index'));

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('asset cannot be checked out to itself', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'asset',
            'assigned_asset' => $asset->id,
        ])
        ->assertSessionHas('error');

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('validation when checking out asset', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('hardware.checkout.store', Asset::factory()->create()), [
            'status_id' => 'does-not-exist',
            'checkout_at' => 'invalid-date',
            'expected_checkin' => 'invalid-date',
        ])
        ->assertSessionHasErrors([
            'assigned_user',
            'assigned_asset',
            'assigned_location',
            'status_id',
            'checkout_to_type',
            'checkout_at',
            'expected_checkin',
        ]);

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('cannot checkout across companies when full company support enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $assetCompany = Company::factory()->create();
    $userCompany = Company::factory()->create();

    $user = User::factory()->forCompany($userCompany)->create();
    $asset = Asset::factory()->for($assetCompany)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])
        ->assertRedirect(route('hardware.checkout.store', $asset));

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('can checkout to user with multiple companies via pivot when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    // User's primary company is A but is also assigned to B via pivot
    $user = User::factory()->forCompany($companyA)->create();
    $user->companies()->sync([$companyA->id, $companyB->id]);

    // Asset belongs to company B
    $asset = Asset::factory()->for($companyB)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])
        ->assertRedirect();

    Event::assertDispatched(CheckoutableCheckedOut::class);
});

test('can checkout to uncompanied location when fmcs enabled without location scoping', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $location = Location::factory()->create(['company_id' => null]);
    $asset = Asset::factory()->for($company)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'location',
            'assigned_location' => $location->id,
            'redirect_option' => 'index',
        ])
        ->assertSessionMissing('error')
        ->assertRedirect();

    Event::assertDispatched(CheckoutableCheckedOut::class);
});

test('can checkout to child location whose parent has matching company when location scoping enabled', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $company = Company::factory()->create();
    $parentLocation = Location::factory()->for($company)->create();
    $childLocation = Location::factory()->create(['parent_id' => $parentLocation->id, 'company_id' => null]);
    $asset = Asset::factory()->for($company)->create(['rtd_location_id' => $parentLocation->id]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'location',
            'assigned_location' => $childLocation->id,
            'redirect_option' => 'index',
        ])
        ->assertSessionMissing('error')
        ->assertRedirect();

    Event::assertDispatched(CheckoutableCheckedOut::class);
});

test('cannot checkout to child location whose parent has different company when location scoping enabled', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $assetCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $parentLocation = Location::factory()->for($otherCompany)->create();
    $childLocation = Location::factory()->create(['parent_id' => $parentLocation->id, 'company_id' => null]);
    $asset = Asset::factory()->for($assetCompany)->create(['rtd_location_id' => null]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'location',
            'assigned_location' => $childLocation->id,
        ])
        ->assertSessionHas('error');

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.checkout.create', Asset::factory()->create()))
        ->assertOk();
});

test('asset can be checked out', function ($checkoutType, $target, $expectedLocation) {
    $newStatus = Statuslabel::factory()->readyToDeploy()->create();
    $asset = Asset::factory()->create();
    $admin = User::factory()->checkoutAssets()->create();

    $defaultFieldsAlwaysIncludedInUIFormSubmission = [
        'assigned_user' => null,
        'assigned_asset' => null,
        'assigned_location' => null,
    ];

    $this->actingAs($admin)
        ->post(route('hardware.checkout.store', $asset), array_merge($defaultFieldsAlwaysIncludedInUIFormSubmission, [
            'checkout_to_type' => $checkoutType,
            // overwrite the value from the default fields set above
            'assigned_'.$checkoutType => (string) $target->id,
            'name' => 'Changed Name',
            'status_id' => (string) $newStatus->id,
            'checkout_at' => '2024-03-18',
            'expected_checkin' => '2024-03-28',
            'note' => 'An awesome note',
        ]));

    $asset->refresh();
    expect($asset->assignedTo()->is($target))->toBeTrue();
    expect($asset->location->is($expectedLocation))->toBeTrue();
    expect($asset->name)->toEqual('Changed Name');
    expect($asset->status->is($newStatus))->toBeTrue();
    expect($asset->last_checkout)->toEqual('2024-03-18 00:00:00');
    expect((string) $asset->expected_checkin)->toEqual('2024-03-28 00:00:00');

    Event::assertDispatched(CheckoutableCheckedOut::class, 1);
    Event::assertDispatched(function (CheckoutableCheckedOut $event) use ($admin, $asset, $target) {
        expect($event->checkoutable->is($asset))->toBeTrue();
        expect($event->checkedOutTo->is($target))->toBeTrue();
        expect($event->checkedOutBy->is($admin))->toBeTrue();
        expect($event->note)->toEqual('An awesome note');

        return true;
    });
    $this->assertHasTheseActionLogs($asset, ['create'/* , 'checkout' */]); // TODO - only getting one?
})->with([
    'User' => [
        function () {
            $userLocation = Location::factory()->create();
            $user = User::factory()->for($userLocation)->create();

            return [
                'checkout_type' => 'user',
                'target' => $user,
                'expected_location' => $userLocation,
            ];
        },
    ],
    'Asset without location set' => [
        function () {
            $rtdLocation = Location::factory()->create();
            $asset = Asset::factory()->for($rtdLocation, 'defaultLoc')->create(['location_id' => null]);

            return [
                'checkout_type' => 'asset',
                'target' => $asset,
                'expected_location' => $rtdLocation,
            ];
        },
    ],
    'Asset with location set' => [
        function () {
            $rtdLocation = Location::factory()->create();
            $location = Location::factory()->create();
            $asset = Asset::factory()->for($location)->for($rtdLocation, 'defaultLoc')->create();

            return [
                'checkout_type' => 'asset',
                'target' => $asset,
                'expected_location' => $location,
            ];
        },
    ],
    'Location' => [
        function () {
            $location = Location::factory()->create();

            return [
                'checkout_type' => 'location',
                'target' => $location,
                'expected_location' => $location,
            ];
        },
    ],
]);

test('license seats are assigned to user upon checkout', function () {
    $asset = Asset::factory()->create();
    $seat = LicenseSeat::factory()->assignedToAsset($asset)->create();
    $user = User::factory()->create();

    expect($user->licenses->contains($seat->license))->toBeFalse();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]);

    expect($user->fresh()->licenses->contains($seat->license))->toBeTrue();
});

test('checkout can set asset to not requestable', function () {
    Event::fakeExcept([CheckoutableCheckedOut::class]);

    $asset = Asset::factory()->create(['requestable' => 1]);
    $targetUser = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
            // Unchecked checkbox on the form → key absent → boolean()
            // returns false → asset flipped to not-requestable.
        ]);

    expect((bool) $asset->fresh()->requestable)->toBeFalse();

    $log = Actionlog::query()
        ->where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->where('action_type', 'checkout')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->log_meta)->not->toBeNull();

    $logMeta = json_decode($log->log_meta, true);
    expect($logMeta)->toHaveKey('requestable');
    expect((int) $logMeta['requestable']['old'])->toEqual(1);
    expect((int) $logMeta['requestable']['new'])->toEqual(0);
});

test('last checkout uses current date if not provided', function () {
    $asset = Asset::factory()->create(['last_checkout' => now()->subMonth()]);

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ]);

    $asset->refresh();

    expect((int) Carbon::parse($asset->last_checkout)->diffInSeconds(now(), true) < 2)->toBeTrue();
});

test('asset checkout page is redirected if model is invalid', function () {
    $asset = Asset::factory()->create();
    $asset->model_id = 0;
    $asset->forceSave();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('hardware.checkout.create', $asset))
        ->assertStatus(302)
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.show', $asset));
});

test('asset checkout page post is redirected if redirect selection is index', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.checkout.create', $asset))
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
            'redirect_option' => 'index',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.index'));
});

test('asset checkout page post is redirected if redirect selection is item', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.checkout.create', $asset))
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
            'redirect_option' => 'item',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('hardware.show', $asset));
});

test('asset checkout page post is redirected if redirect selection is user target', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.checkout.create', $asset))
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'redirect_option' => 'target',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('users.show', ['user' => $user]));
});

test('asset checkout page post is redirected if redirect selection is asset target', function () {
    $target = Asset::factory()->create();
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.checkout.create', $asset))
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'asset',
            'assigned_asset' => $target->id,
            'redirect_option' => 'target',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.show', $target));
});

test('asset checkout page post is redirected if redirect selection is location target', function () {
    $target = Location::factory()->create();
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.checkout.create', $asset))
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'location',
            'assigned_location' => $target->id,
            'redirect_option' => 'target',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('locations.show', ['location' => $target]));
});

test('asset checkout page post redirects to signature page when sign in place is checked', function () {
    $targetUser = User::factory()->create();
    $asset = Asset::factory()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.checkout.create', $asset))
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'sign_in_place' => 1,
        ]);

    $acceptance = CheckoutAcceptance::query()
        ->where('checkoutable_type', Asset::class)
        ->where('checkoutable_id', $asset->id)
        ->where('assigned_to_id', $targetUser->id)
        ->pending()
        ->latest()
        ->first();

    expect($acceptance)->not->toBeNull();
    expect($acceptance->qty)->toBeNull();
    $this->assertDatabaseCount('checkout_acceptances', 1);

    $response->assertStatus(302)
        ->assertRedirect(route('account.accept.item', $acceptance));
});

test('asset checkout stores sign in place preference in session', function () {
    $targetUser = User::factory()->create();
    $asset = Asset::factory()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
            'redirect_option' => 'index',
            'sign_in_place' => 1,
        ]);

    $response->assertSessionHas('sign_in_place', true);
});

test('asset checkout stores target type as string in session', function (string $type) {
    [$field, $target] = match ($type) {
        'user' => ['assigned_user', User::factory()->create()->id],
        'asset' => ['assigned_asset', Asset::factory()->create()->id],
        'location' => ['assigned_location', Location::factory()->create()->id],
    };
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => $type,
            $field => $target,
        ])
        ->assertSessionHasNoErrors();

    $stored = session('checkout_to_type');
    expect($stored)->toBeString('checkout_to_type must be a string, not an Eloquent model');
    expect($stored)->toBe($type);
})->with([
    'user target' => ['user'],
    'asset target' => ['asset'],
    'location target' => ['location'],
]);

test('license seats are not reassigned when cross company checkout is rejected', function () {
    // The company-boundary gate has to fire before the license-seat
    // loop persists $seat->assigned_to = $target->id. Prior to the
    // ordering fix the seat writes ran first, so a cross-company
    // checkout attempt would leave the seat reassigned to a user in
    // a different company even though the checkout itself was
    // refused.
    $this->settings->enableMultipleFullCompanySupport();

    [$assetCompany, $userCompany] = Company::factory()->count(2)->create();

    $asset = Asset::factory()->for($assetCompany)->create();
    $seat = LicenseSeat::factory()->assignedToAsset($asset)->create();
    $originalSeatAssignedTo = $seat->assigned_to;

    $user = User::factory()->forCompany($userCompany)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])
        ->assertRedirect(route('hardware.checkout.store', $asset));

    Event::assertNotDispatched(CheckoutableCheckedOut::class);

    expect($seat->fresh()->assigned_to)->toBe($originalSeatAssignedTo, 'License seat must not be reassigned when the parent checkout is refused for a cross-company target.');
});

test('asset location defaults to target user location when override is empty', function () {
    $userLocation = Location::factory()->create();
    $user = User::factory()->for($userLocation)->create();
    $asset = Asset::factory()->create(['location_id' => null]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'location_id' => '',
            'rtd_location_id' => '',
        ])
        ->assertRedirect();

    expect($asset->fresh()->location_id)->toBe($userLocation->id);
});

test('pre filled location picker submits assets current location over target', function () {
    $assetLocation = Location::factory()->create();
    $userLocation = Location::factory()->create();
    $user = User::factory()->for($userLocation)->create();
    $asset = Asset::factory()->create(['location_id' => $assetLocation->id]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'location_id' => $asset->location_id,
            'rtd_location_id' => $asset->rtd_location_id,
        ])
        ->assertRedirect();

    expect($asset->fresh()->location_id)->toBe($assetLocation->id);
});

test('admin can override asset location during checkout to user', function () {
    $userLocation = Location::factory()->create();
    $overrideLocation = Location::factory()->create();
    $user = User::factory()->for($userLocation)->create();
    $asset = Asset::factory()->create(['location_id' => null]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'location_id' => $overrideLocation->id,
        ])
        ->assertRedirect();

    $fresh = $asset->fresh();
    expect($fresh->location_id)->toBe($overrideLocation->id);
    $this->assertNotSame($userLocation->id, $fresh->location_id);
});

test('admin can override rtd location during checkout', function () {
    $originalRtd = Location::factory()->create();
    $overrideRtd = Location::factory()->create();
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['rtd_location_id' => $originalRtd->id]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'rtd_location_id' => $overrideRtd->id,
        ])
        ->assertRedirect();

    expect($asset->fresh()->rtd_location_id)->toBe($overrideRtd->id);
});

test('invalid location override bounces the checkout', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'location_id' => 999999,
        ])
        ->assertSessionHas('error');

    expect($asset->fresh()->assigned_to)->toBeNull();
    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('asset checkout rejects when attached seat is unreassignable', function () {
    $previousHolder = User::factory()->create();
    $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
    $seat = $license->licenseseats()->first();
    $asset = Asset::factory()->create();
    $seat->asset_id = $asset->id;
    $seat->assigned_to = $previousHolder->id;
    $seat->unreassignable_seat = true;
    $seat->save();

    $newHolder = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $newHolder->id,
        ])
        ->assertSessionHas('error');

    expect((int) $seat->fresh()->assigned_to)->toBe($previousHolder->id);
    expect($asset->fresh()->assigned_to)->toBeNull();
});

test('asset checkout rejects when license is not reassignable and seat is occupied', function () {
    $previousHolder = User::factory()->create();
    $license = License::factory()->create(['seats' => 1, 'reassignable' => false]);
    $seat = $license->licenseseats()->first();
    $asset = Asset::factory()->create();
    $seat->asset_id = $asset->id;
    $seat->assigned_to = $previousHolder->id;
    $seat->save();

    $newHolder = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $newHolder->id,
        ])
        ->assertSessionHas('error');

    expect((int) $seat->fresh()->assigned_to)->toBe($previousHolder->id);
    expect($asset->fresh()->assigned_to)->toBeNull();
});

test('asset checkout fires checkin event for displaced seat holder', function () {
    Event::fake([CheckoutableCheckedIn::class, CheckoutableCheckedOut::class]);

    $previousHolder = User::factory()->create();
    $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
    $seat = $license->licenseseats()->first();
    $asset = Asset::factory()->create();
    $seat->asset_id = $asset->id;
    $seat->assigned_to = $previousHolder->id;
    $seat->save();

    $newHolder = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $newHolder->id,
        ]);

    // Seat moves to the new holder, previous holder gets a checkin event.
    expect((int) $seat->fresh()->assigned_to)->toBe($newHolder->id);
    Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($seat, $previousHolder) {
        return $event->checkoutable->is($seat) && $event->checkedOutTo->is($previousHolder);
    });
});

test('asset checkout deletes pending acceptances of displaced seat holder', function () {
    $previousHolder = User::factory()->create();
    $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
    $seat = $license->licenseseats()->first();
    $asset = Asset::factory()->create();
    $seat->asset_id = $asset->id;
    $seat->assigned_to = $previousHolder->id;
    $seat->save();

    $pendingAcceptance = new CheckoutAcceptance;
    $pendingAcceptance->checkoutable_type = LicenseSeat::class;
    $pendingAcceptance->checkoutable_id = $seat->id;
    $pendingAcceptance->assigned_to_id = $previousHolder->id;
    $pendingAcceptance->save();

    $newHolder = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $newHolder->id,
        ]);

    expect(CheckoutAcceptance::find($pendingAcceptance->id))->toBeNull('Displaced seat holder\'s pending acceptance must be cleared so the audit trail does not carry a stale acceptance for a seat they no longer hold.');
});

test('asset checkout does not fire checkin event when seat was already assigned to same user', function () {
    Event::fake([CheckoutableCheckedIn::class, CheckoutableCheckedOut::class]);

    $target = User::factory()->create();
    $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
    $seat = $license->licenseseats()->first();
    $asset = Asset::factory()->create();
    $seat->asset_id = $asset->id;
    $seat->assigned_to = $target->id;
    $seat->save();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $target->id,
        ]);

    expect((int) $seat->fresh()->assigned_to)->toBe($target->id);
    Event::assertNotDispatched(CheckoutableCheckedIn::class);
});
