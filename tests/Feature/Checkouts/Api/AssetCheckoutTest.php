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
use Tests\TestCase;

class AssetCheckoutTest extends TestCase
{
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

        $this->assertHasTheseActionLogs($requestable, ['create', 'requested', 'update']); // FIXME - is this right?!

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

    public function test_cannot_checkout_across_companies_when_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actorInCompanyA = User::factory()->checkoutAssets()->for($companyA)->create();
        $assetInCompanyA = Asset::factory()->for($companyA)->create();
        $userInCompanyB = User::factory()->for($companyB)->create();

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

        $actorInCompanyA = User::factory()->checkoutAssets()->for($companyA)->create();
        $assetInCompanyA = Asset::factory()->for($companyA)->create();
        $userInCompanyB = User::factory()->for($companyB)->create();

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
                        'expected_location' => null,
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

    public function test_license_seats_are_assigned_to_user_upon_checkout()
    {
        $admin = User::factory()->checkoutAssets()->create();
        $targetUser = User::factory()->create();

        // Crear un asset disponible para checkout
        $asset = Asset::factory()->create();

        // Crear una licencia con 3 seats y asignar uno al asset
        $this->actingAs($admin);
        $license = \App\Models\License::factory()->create(['seats' => 3]);

        // Obtener el primer seat libre y asignarlo al asset
        $seat = $license->freeSeat();
        $this->assertNotNull($seat, 'Debe haber un seat libre para este test');

        $seat->asset_id = $asset->id;
        $seat->save();

        // Hacer checkout del asset al usuario

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user'    => $targetUser->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        // Verificar persistencia correcta en base de datos

        // 1. El asset queda asignado al usuario
        $asset->refresh();
        $this->assertEquals($targetUser->id, $asset->assigned_to);
        $this->assertEquals(\App\Models\User::class, $asset->assigned_type);

        // 2. El LicenseSeat sigue vinculado al asset
        $seat->refresh();
        $this->assertEquals($asset->id, $seat->asset_id);

        // 3. El action_log registra el checkout del asset
        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_type' => \App\Models\User::class,
            'target_id'   => $targetUser->id,
            'item_type'   => \App\Models\Asset::class,
            'item_id'     => $asset->id,
        ]);

        // 4. Las relaciones se mantienen íntegras
        $this->assertDatabaseHas('license_seats', [
            'id'         => $seat->id,
            'license_id' => $license->id,
            'asset_id'   => $asset->id,
            'deleted_at' => null,
        ]);
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
}
