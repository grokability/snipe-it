<?php

use App\Mail\BulkAssetCheckoutMail;
use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\ExpectationFailedException;

test('requires permission', function () {
    // The target user must actually exist and be undeleted, or the
    // FormRequest's `exists_undeleted:users,id` rule on `assigned_user`
    // trips first and returns 422 before the controller's permission
    // check ever runs. Rule was added in 8acedc241f (FD-56263).
    $target = User::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => [1],
            'checkout_to_type' => 'user',
            'assigned_user' => $target->id,
            'assigned_asset' => null,
            'checkout_at' => null,
            'expected_checkin' => null,
            'note' => null,
        ])
        ->assertForbidden();
});

test('can bulk checkout assets', function () {
    Mail::fake();

    $assets = Asset::factory()->requiresAcceptance()->count(2)->create();
    $user = User::factory()->create(['email' => 'someone@example.com']);

    $checkoutAt = now()->subWeek()->format('Y-m-d');
    $expectedCheckin = now()->addWeek()->format('Y-m-d');

    $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
        ->followingRedirects()
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'assigned_asset' => null,
            'checkout_at' => $checkoutAt,
            'expected_checkin' => $expectedCheckin,
            'note' => null,
        ])
        ->assertOk();

    $assets = $assets->fresh();

    $assets->each(function ($asset) use ($expectedCheckin, $checkoutAt, $user) {
        $asset->assignedTo()->is($user);
        $asset->last_checkout = $checkoutAt;
        $asset->expected_checkin = $expectedCheckin;
        $this->assertHasTheseActionLogs($asset, ['create', 'checkout']); // Note: '$this' gets auto-bound in closures, so this does work.
        $this->assertDatabaseHas('checkout_acceptances', [
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
            'qty' => 1,
        ]);
    });

    Mail::assertNotSent(CheckoutAssetMail::class);
    Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
        return $mail->hasTo('someone@example.com');
    });
});

test('handle missing model being included', function () {
    Mail::fake();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => [
                Asset::factory()->requiresAcceptance()->create()->id,
                9999999,
            ],
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create(['email' => 'someone@example.com'])->id,
            'assigned_asset' => null,
            'checkout_at' => null,
            'expected_checkin' => null,
            'note' => null,
        ])
        ->assertSessionHas('error', trans_choice('admin/hardware/message.multi-checkout.error', 2));

    try {
        Mail::assertNotSent(CheckoutAssetMail::class);
    } catch (ExpectationFailedException $e) {
        $this->fail('Asset checkout email was sent when the entire checkout failed.');
    }
});

test('bulk checkout sets requestable to false when checkbox unchecked', function () {
    $assets = Asset::factory()->count(2)->create(['requestable' => 1]);
    $targetUser = User::factory()->create();

    // Bulk checkout now mirrors the per-item checkout: `requestable`
    // is a plain checkbox and its posted value (or absence) is
    // authoritative. Omitting the field means unchecked, so every
    // selected asset ends up requestable=false regardless of its
    // prior state.
    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
        ]);

    $assets->each(function (Asset $asset) {
        expect((bool) $asset->fresh()->requestable)->toBeFalse();
    });
});

test('bulk checkout sets requestable to true when checkbox checked', function () {
    $requestableAsset = Asset::factory()->create(['requestable' => 1]);
    $nonRequestableAsset = Asset::factory()->create(['requestable' => 0]);
    $targetUser = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => [$requestableAsset->id, $nonRequestableAsset->id],
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
            'requestable' => '1',
        ]);

    expect((bool) $requestableAsset->fresh()->requestable)->toBeTrue();
    expect((bool) $nonRequestableAsset->fresh()->requestable)->toBeTrue();
});

dataset('checkoutTargets', function () {
    yield 'Checkout to user' => [
        function () {
            return [
                'type' => 'user',
                'target' => User::factory()->create(),
            ];
        },
    ];

    yield 'Checkout to asset' => [
        function () {
            return [
                'type' => 'asset',
                'target' => Asset::factory()->create(),
            ];
        },
    ];

    yield 'Checkout to location' => [
        function () {
            return [
                'type' => 'location',
                'target' => Location::factory()->create(),
            ];
        },
    ];
});

test('adheres to full multiple company support', function ($type, $target) {
    $this->settings->enableMultipleFullCompanySupport();

    // create two companies
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    // create an asset for each company
    $assetForCompanyA = Asset::factory()->for($companyA)->create();
    $assetForCompanyB = Asset::factory()->for($companyB)->create();

    expect($assetForCompanyA->assigned_to)->toBeNull('Asset should not be assigned before attempting this test case.');
    expect($assetForCompanyB->assigned_to)->toBeNull('Asset should not be assigned before attempting this test case.');

    // attempt to bulk checkout both items to the target
    $response = $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => [
                $assetForCompanyA->id,
                $assetForCompanyB->id,
            ],
            'checkout_to_type' => $type,
            "assigned_$type" => $target->id,
        ]);

    // ensure bulk checkout is blocked
    expect($assetForCompanyA->fresh()->assigned_to)->toBeNull('Asset was checked out across companies.');
    expect($assetForCompanyB->fresh()->assigned_to)->toBeNull('Asset was checked out across companies.');

    // ensure redirected back
    $response->assertRedirectToRoute('hardware.bulkcheckout.show');
})->with('checkoutTargets');

test('prevents checkouts of checked out items', function ($type, $target) {
    $asset = Asset::factory()->create();
    $checkedOutAsset = Asset::factory()->assignedToUser()->create();
    $existingUserId = $checkedOutAsset->assigned_to;

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => [
                $asset->id,
                $checkedOutAsset->id,
            ],
            'checkout_to_type' => $type,
            "assigned_$type" => $target->id,
        ]);

    expect($checkedOutAsset->fresh()->assigned_to)->toEqual($existingUserId, 'Asset was checked out when it should have been prevented.');

    // ensure redirected back
    $response->assertRedirectToRoute('hardware.bulkcheckout.show');
})->with('checkoutTargets');

/**
 * Regression: BulkAssetsController::store used to call
 *   session()->put(['checkout_to_type' => $target]);
 * with $target being the resolved Eloquent model. The checkout-selector
 * partial compares against 'user'/'asset'/'location' string literals, so
 * an object silently mismatched and no radio rendered `checked`.
 *
 * @see \App\Http\Controllers\Assets\BulkAssetsController::store
 */
test('bulk checkout defaults to asset index when no redirect option', function () {
    Mail::fake();
    $assets = Asset::factory()->count(2)->create();
    $user = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])
        ->assertRedirect(route('hardware.index'));
});

test('bulk checkout returns to bulk checkout when option selected', function () {
    Mail::fake();
    $assets = Asset::factory()->count(2)->create();
    $user = User::factory()->create();

    $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'redirect_option' => 'bulk_checkout',
        ])
        ->assertRedirect(route('hardware.bulkcheckout.show'));
});

test('bulk checkout stores target type as string in session', function (string $type) {
    [$field, $target] = match ($type) {
        'user' => ['assigned_user', User::factory()->create()->id],
        'asset' => ['assigned_asset', Asset::factory()->create()->id],
        'location' => ['assigned_location', Location::factory()->create()->id],
    };
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.bulkcheckout.store'), [
            'selected_assets' => [$asset->id],
            'checkout_to_type' => $type,
            $field => $target,
        ]);

    $stored = session('checkout_to_type');
    expect($stored)->toBeString('checkout_to_type must be a string, not an Eloquent model');
    expect($stored)->toBe($type);
})->with('bulkCheckoutTargetTypesProvider');

dataset('bulkCheckoutTargetTypesProvider', function () {
    return [
        'user target' => ['user'],
        'asset target' => ['asset'],
        'location target' => ['location'],
    ];
});
