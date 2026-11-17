<?php

namespace Tests\Feature\Checkouts\Api;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MakesWatsonValidationLoud;
use Tests\TestCase;

class AssetCheckoutTest extends TestCase
{
    use MakesWatsonValidationLoud;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([CheckoutableCheckedOut::class]);
    }

    public function test_checkout_request()
    {
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

    }

    public function test_checking_out_asset_requires_correct_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.checkout', Asset::factory()->create()), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_non_existent_asset_cannot_be_checked_out()
    {
        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', 1000), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_asset_not_available_for_checkout_cannot_be_checked_out()
    {
        $assetAlreadyCheckedOut = Asset::factory()->assignedToUser()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $assetAlreadyCheckedOut), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_asset_cannot_be_checked_out_to_itself()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $asset->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_validation_when_checking_out_asset()
    {
        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', Asset::factory()->create()), [])
            ->assertStatusMessageIs('error');

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function test_asset_checkout_infers_user_target_when_checkout_to_type_omitted()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'assigned_user' => $user->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertTrue($asset->assignedTo()->is($user));
    }

    public function test_asset_checkout_infers_asset_target_when_checkout_to_type_omitted()
    {
        $asset = Asset::factory()->create();
        $target = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'assigned_asset' => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertTrue($asset->assignedTo()->is($target));
    }

    public function test_asset_checkout_infers_location_target_when_checkout_to_type_omitted()
    {
        $asset = Asset::factory()->create();
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'assigned_location' => $location->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertTrue($asset->assignedTo()->is($location));
    }

    public function test_asset_checkout_rejects_multiple_target_fields()
    {
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

        $this->assertArrayHasKey('assigned_user', $response->json('messages'));

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
    }

    public function test_cannot_checkout_across_companies_when_full_company_support_enabled()
    {
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

        $this->assertNull($assetInCompanyA->assigned_to);
        $this->assertNull($assetInCompanyA->assigned_type);
        $this->assertEquals(0, $assetInCompanyA->checkout_counter);

        $this->assertDatabaseMissing('action_logs', [
            'created_by' => $actorInCompanyA->id,
            'action_type' => 'checkout',
            'target_type' => User::class,
            'target_id' => $userInCompanyB->id,
            'item_type' => Asset::class,
            'item_id' => $assetInCompanyA->id,
        ]);
    }

    public function test_checkout_by_tag_cannot_checkout_across_companies_when_full_company_support_enabled()
    {
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

        $this->assertNull($assetInCompanyA->assigned_to);
        $this->assertNull($assetInCompanyA->assigned_type);
        $this->assertEquals(0, $assetInCompanyA->checkout_counter);

        $this->assertDatabaseMissing('action_logs', [
            'created_by' => $actorInCompanyA->id,
            'action_type' => 'checkout',
            'target_type' => User::class,
            'target_id' => $userInCompanyB->id,
            'item_type' => Asset::class,
            'item_id' => $assetInCompanyA->id,
        ]);
    }

    /**
     * This data provider contains checkout targets along with the
     * asset's expected location after the checkout process.
     */
    public static function checkoutTargets(): array
    {
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
    }

    #[DataProvider('checkoutTargets')]
    public function test_asset_can_be_checked_out($data)
    {
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
        $this->assertTrue($asset->assignedTo()->is($target));
        $this->assertEquals('Changed Name', $asset->name);
        $this->assertTrue($asset->status->is($newStatus));
        $this->assertEquals('2024-04-01 00:00:00', $asset->last_checkout);
        $this->assertEquals('2024-04-08 00:00:00', (string) $asset->expected_checkin);

        $expectedLocation
            ? $this->assertTrue($asset->location->is($expectedLocation))
            : $this->assertNull($asset->location);

        Event::assertDispatched(CheckoutableCheckedOut::class, 1);
        Event::assertDispatched(function (CheckoutableCheckedOut $event) use ($admin, $asset, $target) {
            $this->assertTrue($event->checkoutable->is($asset));
            $this->assertTrue($event->checkedOutTo->is($target));
            $this->assertTrue($event->checkedOutBy->is($admin));
            $this->assertEquals('Here is a cool note!', $event->note);

            return true;
        });
    }

    public function test_asset_can_be_checked_out_to_user_in_same_company_via_pivot_when_fmcs_enabled()
    {
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
        $this->assertEquals($target->id, $assetInCompanyC->assigned_to);
    }

    public function test_asset_cannot_be_checked_out_to_user_whose_companies_exclude_asset_company_when_fmcs_enabled()
    {
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
        $this->assertNull($assetInCompanyC->assigned_to);
    }

    public function test_asset_can_be_checked_out_to_user_with_no_company_when_fmcs_enabled()
    {
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
        $this->assertEquals($target->id, $assetInCompany->assigned_to);
    }

    public function test_license_seats_are_assigned_to_user_upon_checkout()
    {
        $this->markTestIncomplete('This is not implemented');
    }

    public function test_last_checkout_uses_current_date_if_not_provided()
    {
        $asset = Asset::factory()->create(['last_checkout' => now()->subMonth()]);

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ]);

        $asset->refresh();

        $this->assertTrue((int) Carbon::parse($asset->last_checkout)->diffInSeconds(now(), true) < 2);
    }

    public function test_api_checkout_can_update_requestable_when_field_is_passed()
    {
        $asset = Asset::factory()->create(['requestable' => 1]);
        $targetUser = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
                'requestable' => 0,
            ])
            ->assertStatusMessageIs('success');

        $this->assertFalse((bool) $asset->fresh()->requestable);
    }

    public function test_api_checkout_leaves_requestable_unchanged_when_field_is_omitted()
    {
        $asset = Asset::factory()->create(['requestable' => 1]);
        $targetUser = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertTrue((bool) $asset->fresh()->requestable);
    }

    public function test_null_company_asset_cannot_be_checked_out_to_companied_user_when_fmcs_enabled_without_floater()
    {
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

        $this->assertNull($nullCompanyAsset->fresh()->assigned_to);
    }

    public function test_null_company_asset_can_be_checked_out_to_companied_user_when_floater_enabled()
    {
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

        $this->assertEquals($companiedUser->id, $nullCompanyAsset->fresh()->assigned_to);
    }

    /**
     * GHSA-6hxf-hqrc-wfrp regression coverage: the reporter's PoC
     * exactly. An empty-pivot ("null tenant") actor holding assets.checkout
     * would previously succeed at checking out a null-company asset to a
     * companied user in any other company under strict FMCS, because
     * CompanyableTrait::canCheckoutTo used $target->companies()->count()
     * to detect an uncompanied target. That subquery re-applied the actor's
     * Company::CompanyableScope, which for an empty-pivot actor degenerates
     * to whereNull('companies.id') and matches zero rows, making every
     * target look pivot-less. The fix delegates to
     * User::canReceiveFromCompany(null), which reads company_user directly
     * via DB::table and correctly rejects a companied target under strict
     * mode.
     */
    public function test_empty_pivot_actor_cannot_check_out_null_company_asset_to_companied_user_under_strict_fmcs()
    {
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

        $this->assertNull(
            $nullCompanyAsset->fresh()->assigned_to,
            'Strict FMCS must reject a null-company asset being checked out to a companied user by an empty-pivot actor.'
        );

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function test_empty_pivot_actor_can_check_out_null_company_asset_to_empty_pivot_user_under_strict_fmcs()
    {
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

        $this->assertEquals($emptyPivotTarget->id, $nullCompanyAsset->fresh()->assigned_to);
    }

    public function test_empty_pivot_actor_can_check_out_null_company_asset_to_companied_user_under_floater_mode()
    {
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

        $this->assertEquals($companiedTarget->id, $nullCompanyAsset->fresh()->assigned_to);
    }
}
