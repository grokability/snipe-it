<?php

namespace Tests\Feature\Checkouts\Api;

use Illuminate\Support\Facades\Mail;
use Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssetCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([CheckoutableCheckedOut::class]);
    }

    public function testCheckoutRequest()
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

        $this->assertHasTheseActionLogs($requestable, ['create', 'requested', 'update']); //FIXME - is this right?!

    }

    public function testCheckingOutAssetRequiresCorrectPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.checkout', Asset::factory()->create()), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function testNonExistentAssetCannotBeCheckedOut()
    {
        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', 1000), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function testAssetNotAvailableForCheckoutCannotBeCheckedOut()
    {
        $assetAlreadyCheckedOut = Asset::factory()->assignedToUser()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $assetAlreadyCheckedOut), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function testAssetCannotBeCheckedOutToItself()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $asset->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function testValidationWhenCheckingOutAsset()
    {
        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', Asset::factory()->create()), [])
            ->assertStatusMessageIs('error');

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function testCannotCheckoutAcrossCompaniesWhenFullCompanySupportEnabled()
    {
        $this->markTestIncomplete('This is not implemented');
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
                }
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
                }
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
                }
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
                }
            ],
            'Checkout to Location' => [
                function () {
                    $location = Location::factory()->create();

                    return [
                        'checkout_type' => 'location',
                        'target' => $location,
                        'expected_location' => $location,
                    ];
                }
            ],
        ];
    }

    #[DataProvider('checkoutTargets')]
    public function testAssetCanBeCheckedOut($data)
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
        $this->assertTrue($asset->assetstatus->is($newStatus));
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

    public function testLicenseSeatsAreAssignedToUserUponCheckout()
    {
        $this->markTestIncomplete('This is not implemented');
    }

    public function testLastCheckoutUsesCurrentDateIfNotProvided()
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
}
