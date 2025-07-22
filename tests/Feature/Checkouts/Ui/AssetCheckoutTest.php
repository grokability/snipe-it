<?php

namespace Tests\Feature\Checkouts\Ui;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Events\CheckoutableCheckedOut;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\LicenseSeat;
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

    public function testCheckingOutAssetRequiresCorrectPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hardware.checkout.store', Asset::factory()->create()), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function testNonExistentAssetCannotBeCheckedOut()
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

    public function testAssetNotAvailableForCheckoutCannotBeCheckedOut()
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

    public function testAssetCannotBeCheckedOutToItself()
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

    public function testValidationWhenCheckingOutAsset()
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

    public function testCannotCheckoutAcrossCompaniesWhenFullCompanySupportEnabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $assetCompany = Company::factory()->create();
        $userCompany = Company::factory()->create();

        $user = User::factory()->for($userCompany)->create();
        $asset = Asset::factory()->for($assetCompany)->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
            ])
            ->assertRedirect(route('hardware.checkout.store', $asset));

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function testPageRenders()
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
    public function testAssetCanBeCheckedOut($data)
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
                'assigned_' . $type => (string) $target->id,
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
        $this->assertTrue($asset->assetstatus->is($newStatus));
        $this->assertEquals('2024-03-18 00:00:00', $asset->last_checkout);
        $this->assertEquals('2024-03-28 00:00:00', (string)$asset->expected_checkin);

        Event::assertDispatched(CheckoutableCheckedOut::class, 1);
        Event::assertDispatched(function (CheckoutableCheckedOut $event) use ($admin, $asset, $target) {
            $this->assertTrue($event->checkoutable->is($asset));
            $this->assertTrue($event->checkedOutTo->is($target));
            $this->assertTrue($event->checkedOutBy->is($admin));
            $this->assertEquals('An awesome note', $event->note);

            return true;
        });
        $this->assertHasTheseActionLogs($asset, ['create'/*, 'checkout'*/]); //TODO - only getting one?
    }

    public function testLicenseSeatsAreAssignedToUserUponCheckout()
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

    public function testLastCheckoutUsesCurrentDateIfNotProvided()
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

    public function testAssetCheckoutPageIsRedirectedIfModelIsInvalid()
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

    public function testAssetCheckoutPagePostIsRedirectedIfRedirectSelectionIsIndex()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.checkout.create', $asset))
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' =>  User::factory()->create()->id,
                'redirect_option' => 'index',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.index'));
    }

    public function testAssetCheckoutPagePostIsRedirectedIfRedirectSelectionIsItem()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.checkout.create', $asset))
            ->post(route('hardware.checkout.store' , $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' =>  User::factory()->create()->id,
                'redirect_option' => 'item',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('hardware.show', $asset));
    }

    public function testAssetCheckoutPagePostIsRedirectedIfRedirectSelectionIsUserTarget()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.checkout.create', $asset))
            ->post(route('hardware.checkout.store' , $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' =>  $user->id,
                'redirect_option' => 'target',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('users.show', ['user' => $user]));
    }

    public function testAssetCheckoutPagePostIsRedirectedIfRedirectSelectionIsAssetTarget()
    {
        $target = Asset::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.checkout.create', $asset))
            ->post(route('hardware.checkout.store' , $asset), [
                'checkout_to_type' => 'asset',
                'assigned_asset' =>  $target->id,
                'redirect_option' => 'target',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.show', $target));
    }

    public function testAssetCheckoutPagePostIsRedirectedIfRedirectSelectionIsLocationTarget()
    {
        $target = Location::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('hardware.checkout.create', $asset))
            ->post(route('hardware.checkout.store' , $asset), [
                'checkout_to_type' => 'location',
                'assigned_location' =>  $target->id,
                'redirect_option' => 'target',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('locations.show', ['location' => $target]));
    }
}
