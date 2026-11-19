<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\Support\MakesWatsonValidationLoud;

uses(MakesWatsonValidationLoud::class);

beforeEach(function () {
    Event::fake([CheckoutableCheckedOut::class]);
});

test('checkout request', function () {
    Notification::fake();
    $requestable = Asset::factory()->requestable()->create();
    $nonRequestable = Asset::factory()->nonrequestable()->create();

    $this->actingAsForApi(User::factory()->create())
        ->post(route('api.assets.requests.store', $requestable->id))
        ->assertStatusMessageIs('success');

    $this->actingAsForApi(User::factory()->create())
        ->post(route('api.assets.requests.store', $nonRequestable->id))
        ->assertStatusMessageIs('error');

    // The counter increment on requests_counter used to fire a
    // redundant `update` log alongside `requested`; the
    // AssetObserver's same_requests_counter gate now suppresses
    // it (matching how it already suppresses the sibling
    // `update` log on checkout/checkin counter bumps).
    $this->assertHasTheseActionLogs($requestable, ['create', 'requested']);
});

test('checking out asset requires correct permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.asset.checkout', Asset::factory()->create()), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertForbidden();
});

test('non existent asset cannot be checked out', function () {
    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', 1000), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertStatusMessageIs('error');
});

test('asset not available for checkout cannot be checked out', function () {
    $assetAlreadyCheckedOut = Asset::factory()->assignedToUser()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $assetAlreadyCheckedOut), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertStatusMessageIs('error');
});

test('asset cannot be checked out to itself', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'asset',
            'assigned_asset' => $asset->id,
        ])
        ->assertStatusMessageIs('error');
});

test('validation when checking out asset', function () {
    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', Asset::factory()->create()), [])
        ->assertStatusMessageIs('error');

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('asset checkout infers user target when checkout to type omitted', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'assigned_user' => $user->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset->refresh();
    expect($asset->assignedTo()->is($user))->toBeTrue();
});

test('asset checkout infers asset target when checkout to type omitted', function () {
    $asset = Asset::factory()->create();
    $target = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'assigned_asset' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset->refresh();
    expect($asset->assignedTo()->is($target))->toBeTrue();
});

test('asset checkout infers location target when checkout to type omitted', function () {
    $asset = Asset::factory()->create();
    $location = Location::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'assigned_location' => $location->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset->refresh();
    expect($asset->assignedTo()->is($location))->toBeTrue();
});

test('asset checkout rejects multiple target fields', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $response = $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'assigned_user' => $user->id,
            'assigned_location' => $location->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    expect($response->json('messages'))->toHaveKey('assigned_user');

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
});

test('cannot checkout across companies when full company support enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $actorInCompanyA = User::factory()->checkoutAssets()->forCompany($companyA)->create();
    $assetInCompanyA = Asset::factory()->for($companyA)->create();
    $userInCompanyB = User::factory()->forCompany($companyB)->create();

    $this->actingAsForApi($actorInCompanyA)
        ->postJson(route('api.asset.checkout', $assetInCompanyA), [
            'checkout_to_type' => 'user',
            'assigned_user' => $userInCompanyB->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    $assetInCompanyA->refresh();

    expect($assetInCompanyA->assigned_to)->toBeNull();
    expect($assetInCompanyA->assigned_type)->toBeNull();
    expect($assetInCompanyA->checkout_counter)->toEqual(0);

    $this->assertDatabaseMissing('action_logs', [
        'created_by' => $actorInCompanyA->id,
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $userInCompanyB->id,
        'item_type' => Asset::class,
        'item_id' => $assetInCompanyA->id,
    ]);
});

test('checkout by tag cannot checkout across companies when full company support enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $actorInCompanyA = User::factory()->checkoutAssets()->forCompany($companyA)->create();
    $assetInCompanyA = Asset::factory()->for($companyA)->create();
    $userInCompanyB = User::factory()->forCompany($companyB)->create();

    $this->actingAsForApi($actorInCompanyA)
        ->postJson(route('api.assets.checkout.bytag', $assetInCompanyA->asset_tag), [
            'checkout_to_type' => 'user',
            'assigned_user' => $userInCompanyB->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    $assetInCompanyA->refresh();

    expect($assetInCompanyA->assigned_to)->toBeNull();
    expect($assetInCompanyA->assigned_type)->toBeNull();
    expect($assetInCompanyA->checkout_counter)->toEqual(0);

    $this->assertDatabaseMissing('action_logs', [
        'created_by' => $actorInCompanyA->id,
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $userInCompanyB->id,
        'item_type' => Asset::class,
        'item_id' => $assetInCompanyA->id,
    ]);
});

/**
 * This data provider contains checkout targets along with the
 * asset's expected location after the checkout process.
 */
dataset('checkoutTargets', function () {
    return [
        'Checkout to User' => [
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
        'Checkout to User without location set' => [
            function () {
                $userLocation = Location::factory()->create();
                $user = User::factory()->for($userLocation)->create(['location_id' => null]);

                return [
                    'checkout_type' => 'user',
                    'target' => $user,
                    'expected_location' => null,
                ];
            },
        ],
        'Checkout to Asset with location set' => [
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
        'Checkout to Asset without location set' => [
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
        'Checkout to Location' => [
            function () {
                $location = Location::factory()->create();

                return [
                    'checkout_type' => 'location',
                    'target' => $location,
                    'expected_location' => $location,
                ];
            },
        ],
    ];
});

test('asset can be checked out', function ($data) {
    ['checkout_type' => $type, 'target' => $target, 'expected_location' => $expectedLocation] = $data();

    $newStatus = Statuslabel::factory()->readyToDeploy()->create();
    $asset = Asset::factory()->forLocation()->create();
    $admin = User::factory()->checkoutAssets()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => $type,
            'assigned_'.$type => $target->id,
            'status_id' => $newStatus->id,
            'checkout_at' => '2024-04-01',
            'expected_checkin' => '2024-04-08',
            'name' => 'Changed Name',
            'note' => 'Here is a cool note!',
        ])
        ->assertOk();

    $asset->refresh();
    expect($asset->assignedTo()->is($target))->toBeTrue();
    expect($asset->name)->toEqual('Changed Name');
    expect($asset->status->is($newStatus))->toBeTrue();
    expect($asset->last_checkout)->toEqual('2024-04-01 00:00:00');
    expect((string) $asset->expected_checkin)->toEqual('2024-04-08 00:00:00');

    $expectedLocation
        ? expect($asset->location->is($expectedLocation))->toBeTrue()
        : expect($asset->location)->toBeNull();

    Event::assertDispatched(CheckoutableCheckedOut::class, 1);
    Event::assertDispatched(function (CheckoutableCheckedOut $event) use ($admin, $asset, $target) {
        expect($event->checkoutable->is($asset))->toBeTrue();
        expect($event->checkedOutTo->is($target))->toBeTrue();
        expect($event->checkedOutBy->is($admin))->toBeTrue();
        expect($event->note)->toEqual('Here is a cool note!');

        return true;
    });
})->with('checkoutTargets');

test('asset can be checked out to user in same company via pivot when fmcs enabled', function () {
    // Regression: company check used to compare asset company_id to user's primary company_id only.
    // Users assigned to multiple companies via the pivot table must be able to receive assets
    // from any of their companies — not just their first/primary one.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

    // Actor is in companyC (same as the asset) so FMCS scoping lets them see and checkout it.
    $actor = User::factory()->checkoutAssets()->forCompany($companyC)->create();
    $assetInCompanyC = Asset::factory()->for($companyC)->create();

    // Target user's primary company is A, but they also belong to C via pivot.
    $target = User::factory()->forCompany($companyA)->create();
    $target->companies()->sync([$companyA->id, $companyB->id, $companyC->id]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkout', $assetInCompanyC), [
            'checkout_to_type' => 'user',
            'assigned_user' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $assetInCompanyC->refresh();
    expect($assetInCompanyC->assigned_to)->toEqual($target->id);
});

test('asset cannot be checked out to user whose companies exclude asset company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

    // Actor is in companyC (same as the asset).
    $actor = User::factory()->checkoutAssets()->forCompany($companyC)->create();
    $assetInCompanyC = Asset::factory()->for($companyC)->create();

    // Target belongs to A and B — not C. Checkout to them should be blocked.
    $target = User::factory()->forCompany($companyA)->create();
    $target->companies()->sync([$companyA->id, $companyB->id]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkout', $assetInCompanyC), [
            'checkout_to_type' => 'user',
            'assigned_user' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    $assetInCompanyC->refresh();
    expect($assetInCompanyC->assigned_to)->toBeNull();
});

test('asset can be checked out to user with no company when fmcs enabled', function () {
    // In floater mode, users with no company associations can receive items from any company.
    $this->settings->enableFloaterMode();

    $company = Company::factory()->create();

    // Actor is in the same company as the asset.
    $actor = User::factory()->checkoutAssets()->forCompany($company)->create();
    $assetInCompany = Asset::factory()->for($company)->create();

    $target = User::factory()->withoutCompany()->create();
    $target->companies()->sync([]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkout', $assetInCompany), [
            'checkout_to_type' => 'user',
            'assigned_user' => $target->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $assetInCompany->refresh();
    expect($assetInCompany->assigned_to)->toEqual($target->id);
});

test('license seats are assigned to user upon checkout', function () {
    $this->markTestIncomplete('This is not implemented');
});

test('last checkout uses current date if not provided', function () {
    $asset = Asset::factory()->create(['last_checkout' => now()->subMonth()]);

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ]);

    $asset->refresh();

    expect((int) Carbon::parse($asset->last_checkout)->diffInSeconds(now(), true) < 2)->toBeTrue();
});

test('api checkout can update requestable when field is passed', function () {
    $asset = Asset::factory()->create(['requestable' => 1]);
    $targetUser = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
            'requestable' => 0,
        ])
        ->assertStatusMessageIs('success');

    expect((bool) $asset->fresh()->requestable)->toBeFalse();
});

test('api checkout leaves requestable unchanged when field is omitted', function () {
    $asset = Asset::factory()->create(['requestable' => 1]);
    $targetUser = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutAssets()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
        ])
        ->assertStatusMessageIs('success');

    expect((bool) $asset->fresh()->requestable)->toBeTrue();
});

test('null company asset cannot be checked out to companied user when fmcs enabled without floater', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    $company = Company::factory()->create();
    $actor = User::factory()->superuser()->create();
    $nullCompanyAsset = Asset::factory()->create(['company_id' => null]);
    $companiedUser = User::factory()->forCompany($company)->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkout', $nullCompanyAsset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $companiedUser->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    expect($nullCompanyAsset->fresh()->assigned_to)->toBeNull();
});

test('null company asset can be checked out to companied user when floater enabled', function () {
    $this->settings->enableFloaterMode();

    $company = Company::factory()->create();
    $actor = User::factory()->superuser()->create();
    $nullCompanyAsset = Asset::factory()->create(['company_id' => null]);
    $companiedUser = User::factory()->forCompany($company)->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.asset.checkout', $nullCompanyAsset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $companiedUser->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($nullCompanyAsset->fresh()->assigned_to)->toEqual($companiedUser->id);
});

test('empty pivot actor cannot check out null company asset to companied user under strict fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    $company = Company::factory()->create();
    $nullCompanyAsset = Asset::factory()->create(['company_id' => null]);
    $companiedTarget = $company->users()->save(User::factory()->create());
    $emptyPivotActor = User::factory()->checkoutAssets()->create();
    $emptyPivotActor->companies()->sync([]);

    $this->actingAsForApi($emptyPivotActor)
        ->postJson(route('api.asset.checkout', $nullCompanyAsset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $companiedTarget->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    expect($nullCompanyAsset->fresh()->assigned_to)->toBeNull('Strict FMCS must reject a null-company asset being checked out to a companied user by an empty-pivot actor.');

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('empty pivot actor can check out null company asset to empty pivot user under strict fmcs', function () {
    // Positive control for the fix. The "both pivot-less" case is the
    // null pseudo-company namespace and stays legitimately allowed.
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    $nullCompanyAsset = Asset::factory()->create(['company_id' => null]);
    $emptyPivotTarget = User::factory()->create();
    $emptyPivotTarget->companies()->sync([]);
    $emptyPivotActor = User::factory()->checkoutAssets()->create();
    $emptyPivotActor->companies()->sync([]);

    $this->actingAsForApi($emptyPivotActor)
        ->postJson(route('api.asset.checkout', $nullCompanyAsset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $emptyPivotTarget->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($nullCompanyAsset->fresh()->assigned_to)->toEqual($emptyPivotTarget->id);
});

test('empty pivot actor can check out null company asset to companied user under floater mode', function () {
    // Floater mode is the setting that legitimately opens null-company
    // items to companied users. The fix must not accidentally block this.
    $this->settings->enableFloaterMode();

    $company = Company::factory()->create();
    $nullCompanyAsset = Asset::factory()->create(['company_id' => null]);
    $companiedTarget = $company->users()->save(User::factory()->create());
    $emptyPivotActor = User::factory()->checkoutAssets()->create();
    $emptyPivotActor->companies()->sync([]);

    $this->actingAsForApi($emptyPivotActor)
        ->postJson(route('api.asset.checkout', $nullCompanyAsset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $companiedTarget->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($nullCompanyAsset->fresh()->assigned_to)->toEqual($companiedTarget->id);
});
