<?php

namespace Tests\Feature\Checkouts\Ui;

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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssetCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([CheckoutableCheckedOut::class]);
    }

    public function test_checking_out_asset_requires_correct_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hardware.checkout.store', Asset::factory()->create()), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_non_existent_asset_cannot_be_checked_out()
    {
        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', 1000), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
                'name' => 'Changed Name',
            ])
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.index'));

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function test_asset_not_available_for_checkout_cannot_be_checked_out()
    {
        $assetAlreadyCheckedOut = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $assetAlreadyCheckedOut), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.index'));

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function test_asset_cannot_be_checked_out_to_itself()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $asset->id,
            ])
            ->assertSessionHas('error');

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function test_validation_when_checking_out_asset()
    {
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
    }

    public function test_cannot_checkout_across_companies_when_full_company_support_enabled()
    {
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
    }

    public function test_can_checkout_to_user_with_multiple_companies_via_pivot_when_fmcs_enabled()
    {
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
    }

    public function test_can_checkout_to_uncompanied_location_when_fmcs_enabled_without_location_scoping()
    {
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
    }

    public function test_can_checkout_to_child_location_whose_parent_has_matching_company_when_location_scoping_enabled()
    {
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
    }

    public function test_cannot_checkout_to_child_location_whose_parent_has_different_company_when_location_scoping_enabled()
    {
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
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.checkout.create', Asset::factory()->create()))
            ->assertOk();
    }

    /**
     * This data provider contains checkout targets along with the
     * asset's expected location after the checkout process.
     */
    public static function checkoutTargets(): array
    {
        return [
            'User' => [function () {
                $userLocation = Location::factory()->create();
                $user = User::factory()->for($userLocation)->create();

                return [
                    'checkout_type' => 'user',
                    'target' => $user,
                    'expected_location' => $userLocation,
                ];
            }],
            'Asset without location set' => [function () {
                $rtdLocation = Location::factory()->create();
                $asset = Asset::factory()->for($rtdLocation, 'defaultLoc')->create(['location_id' => null]);

                return [
                    'checkout_type' => 'asset',
                    'target' => $asset,
                    'expected_location' => $rtdLocation,
                ];
            }],
            'Asset with location set' => [function () {
                $rtdLocation = Location::factory()->create();
                $location = Location::factory()->create();
                $asset = Asset::factory()->for($location)->for($rtdLocation, 'defaultLoc')->create();

                return [
                    'checkout_type' => 'asset',
                    'target' => $asset,
                    'expected_location' => $location,
                ];
            }],
            'Location' => [function () {
                $location = Location::factory()->create();

                return [
                    'checkout_type' => 'location',
                    'target' => $location,
                    'expected_location' => $location,
                ];
            }],
        ];
    }

    #[DataProvider('checkoutTargets')]
    public function test_asset_can_be_checked_out($data)
    {
        ['checkout_type' => $type, 'target' => $target, 'expected_location' => $expectedLocation] = $data();

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
                'checkout_to_type' => $type,
                // overwrite the value from the default fields set above
                'assigned_'.$type => (string) $target->id,
                'name' => 'Changed Name',
                'status_id' => (string) $newStatus->id,
                'checkout_at' => '2024-03-18',
                'expected_checkin' => '2024-03-28',
                'note' => 'An awesome note',
            ]));

        $asset->refresh();
        $this->assertTrue($asset->assignedTo()->is($target));
        $this->assertTrue($asset->location->is($expectedLocation));
        $this->assertEquals('Changed Name', $asset->name);
        $this->assertTrue($asset->status->is($newStatus));
        $this->assertEquals('2024-03-18 00:00:00', $asset->last_checkout);
        $this->assertEquals('2024-03-28 00:00:00', (string) $asset->expected_checkin);

        Event::assertDispatched(CheckoutableCheckedOut::class, 1);
        Event::assertDispatched(function (CheckoutableCheckedOut $event) use ($admin, $asset, $target) {
            $this->assertTrue($event->checkoutable->is($asset));
            $this->assertTrue($event->checkedOutTo->is($target));
            $this->assertTrue($event->checkedOutBy->is($admin));
            $this->assertEquals('An awesome note', $event->note);

            return true;
        });
        $this->assertHasTheseActionLogs($asset, ['create'/* , 'checkout' */]); // TODO - only getting one?
    }

    public function test_license_seats_are_assigned_to_user_upon_checkout()
    {
        $asset = Asset::factory()->create();
        $seat = LicenseSeat::factory()->assignedToAsset($asset)->create();
        $user = User::factory()->create();

        $this->assertFalse($user->licenses->contains($seat->license));

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
            ]);

        $this->assertTrue($user->fresh()->licenses->contains($seat->license));
    }

    public function test_checkout_can_set_asset_to_not_requestable()
    {
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

        $this->assertFalse((bool) $asset->fresh()->requestable);

        $log = Actionlog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'checkout')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->log_meta);

        $logMeta = json_decode($log->log_meta, true);
        $this->assertArrayHasKey('requestable', $logMeta);
        $this->assertEquals(1, (int) $logMeta['requestable']['old']);
        $this->assertEquals(0, (int) $logMeta['requestable']['new']);
    }

    public function test_last_checkout_uses_current_date_if_not_provided()
    {
        $asset = Asset::factory()->create(['last_checkout' => now()->subMonth()]);

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ]);

        $asset->refresh();

        $this->assertTrue((int) Carbon::parse($asset->last_checkout)->diffInSeconds(now(), true) < 2);
    }

    public function test_asset_checkout_page_is_redirected_if_model_is_invalid()
    {

        $asset = Asset::factory()->create();
        $asset->model_id = 0;
        $asset->forceSave();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('hardware.checkout.create', $asset))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('hardware.show', $asset));
    }

    public function test_asset_checkout_page_post_is_redirected_if_redirect_selection_is_index()
    {
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
    }

    public function test_asset_checkout_page_post_is_redirected_if_redirect_selection_is_item()
    {
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
    }

    public function test_asset_checkout_page_post_is_redirected_if_redirect_selection_is_user_target()
    {
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
    }

    public function test_asset_checkout_page_post_is_redirected_if_redirect_selection_is_asset_target()
    {
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
    }

    public function test_asset_checkout_page_post_is_redirected_if_redirect_selection_is_location_target()
    {
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
    }

    public function test_asset_checkout_page_post_redirects_to_signature_page_when_sign_in_place_is_checked()
    {
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

        $this->assertNotNull($acceptance);
        $this->assertNull($acceptance->qty);
        $this->assertDatabaseCount('checkout_acceptances', 1);

        $response->assertStatus(302)
            ->assertRedirect(route('account.accept.item', $acceptance));
    }

    public function test_asset_checkout_stores_sign_in_place_preference_in_session()
    {
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
    }

    /**
     * Regression: AssetCheckoutController::store used to call
     *   session()->put(['checkout_to_type' => $target]);
     * with $target being the resolved Eloquent model (User/Asset/Location),
     * not the string 'user'/'asset'/'location'. Every subsequent read of
     * session('checkout_to_type') then compared an Eloquent instance to a
     * string literal in the checkout-selector partial, which silently
     * failed — no radio was rendered `checked`, no target select got
     * `required`, and jQuery Validate had nothing to block on.
     *
     * @see \App\Http\Controllers\Assets\AssetCheckoutController::store
     */
    #[DataProvider('checkoutTargetTypesProvider')]
    public function test_asset_checkout_stores_target_type_as_string_in_session(string $type)
    {
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
        $this->assertIsString($stored, 'checkout_to_type must be a string, not an Eloquent model');
        $this->assertSame($type, $stored);
    }

    public static function checkoutTargetTypesProvider(): array
    {
        return [
            'user target' => ['user'],
            'asset target' => ['asset'],
            'location target' => ['location'],
        ];
    }

    public function test_license_seats_are_not_reassigned_when_cross_company_checkout_is_rejected()
    {
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

        $this->assertSame(
            $originalSeatAssignedTo,
            $seat->fresh()->assigned_to,
            'License seat must not be reassigned when the parent checkout is refused for a cross-company target.',
        );
    }

    public function test_asset_location_defaults_to_target_user_location_when_override_is_empty()
    {
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

        $this->assertSame($userLocation->id, $asset->fresh()->location_id);
    }

    public function test_pre_filled_location_picker_submits_assets_current_location_over_target()
    {
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

        $this->assertSame($assetLocation->id, $asset->fresh()->location_id);
    }

    public function test_admin_can_override_asset_location_during_checkout_to_user()
    {
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
        $this->assertSame($overrideLocation->id, $fresh->location_id);
        $this->assertNotSame($userLocation->id, $fresh->location_id);
    }

    public function test_admin_can_override_rtd_location_during_checkout()
    {
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

        $this->assertSame($overrideRtd->id, $asset->fresh()->rtd_location_id);
    }

    public function test_invalid_location_override_bounces_the_checkout()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'location_id' => 999999,
            ])
            ->assertSessionHas('error');

        $this->assertNull($asset->fresh()->assigned_to);
        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    // GHSA-9wq4-gp32-rcgj: AssetCheckoutController::store looped the attached
    // license seats and wrote $seat->assigned_to without any of the guards
    // the license checkout surfaces already enforce (occupied-seat reject,
    // unreassignable_seat reject, license->reassignable reject, no
    // CheckoutableCheckedIn event for displaced holders). The asset
    // checkout path now mirrors those guards and the audit-trail event.

    public function test_asset_checkout_rejects_when_attached_seat_is_unreassignable(): void
    {
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

        $this->assertSame($previousHolder->id, (int) $seat->fresh()->assigned_to);
        $this->assertNull($asset->fresh()->assigned_to);
    }

    public function test_asset_checkout_rejects_when_license_is_not_reassignable_and_seat_is_occupied(): void
    {
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

        $this->assertSame($previousHolder->id, (int) $seat->fresh()->assigned_to);
        $this->assertNull($asset->fresh()->assigned_to);
    }

    public function test_asset_checkout_fires_checkin_event_for_displaced_seat_holder(): void
    {
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
        $this->assertSame($newHolder->id, (int) $seat->fresh()->assigned_to);
        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($seat, $previousHolder) {
            return $event->checkoutable->is($seat) && $event->checkedOutTo->is($previousHolder);
        });
    }

    public function test_asset_checkout_deletes_pending_acceptances_of_displaced_seat_holder(): void
    {
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

        $this->assertNull(
            CheckoutAcceptance::find($pendingAcceptance->id),
            'Displaced seat holder\'s pending acceptance must be cleared so the audit trail does not carry a stale acceptance for a seat they no longer hold.',
        );
    }

    public function test_asset_checkout_does_not_fire_checkin_event_when_seat_was_already_assigned_to_same_user(): void
    {
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

        $this->assertSame($target->id, (int) $seat->fresh()->assigned_to);
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }
}
