<?php

namespace Tests\Feature\Checkins\Api;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssetCheckinTest extends TestCase
{
    public function test_checking_in_asset_requires_correct_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.checkin', Asset::factory()->assignedToUser()->create()))
            ->assertForbidden();
    }

    public function test_cannot_check_in_non_existent_asset()
    {
        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', ['id' => 'does-not-exist']))
            ->assertStatusMessageIs('error');
    }

    public function test_cannot_check_in_asset_that_is_not_checked_out()
    {
        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', Asset::factory()->create()->id))
            ->assertStatusMessageIs('error');
    }

    public function test_asset_can_be_checked_in()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $location = Location::factory()->create();
        $status = Statuslabel::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'expected_checkin' => now()->addDay(),
            'last_checkin' => null,
            'accepted' => 'accepted',
        ]);

        $this->assertTrue($asset->assignedTo->is($user));

        $currentTimestamp = now();

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset), [
                'name' => 'Changed Name',
                'status_id' => $status->id,
                'location_id' => $location->id,
            ])
            ->assertOk();

        $this->assertNull($asset->refresh()->assignedTo);
        $this->assertNull($asset->expected_checkin);
        $this->assertNull($asset->assignedTo);
        $this->assertNull($asset->assigned_type);
        $this->assertNull($asset->accepted);
        $this->assertEquals('Changed Name', $asset->name);
        $this->assertEquals($status->id, $asset->status_id);
        $this->assertTrue($asset->location()->is($location));
        $this->assertHasTheseActionLogs($asset, ['create'/* , 'checkout', 'checkin from' */]); // TODO - the Event::fake() is probably getting in the way here

        Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($currentTimestamp) {
            // this could be better mocked but is ok for now.
            return (int) Carbon::parse($event->action_date)->diffInSeconds($currentTimestamp, true) < 2;
        }, 1);
    }

    public function test_location_is_set_to_rtd_location_by_default_upon_checkin()
    {
        $rtdLocation = Location::factory()->create();
        $asset = Asset::factory()->assignedToUser()->create([
            'location_id' => Location::factory()->create()->id,
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset->id));

        $this->assertTrue($asset->refresh()->location()->is($rtdLocation));
        $this->assertHasTheseActionLogs($asset, ['create', /* 'checkout', */ 'checkin from']); // FIXME?
    }

    public function test_default_location_can_be_updated_upon_checkin()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset), [
                'location_id' => $location->id,
                'update_default_location' => true,
            ]);

        $this->assertTrue($asset->refresh()->defaultLoc()->is($location));
        $this->assertHasTheseActionLogs($asset, ['create', /* 'checkout', */ 'checkin from']); // FIXME?
    }

    public function test_assets_license_seats_are_cleared_upon_checkin()
    {
        $asset = Asset::factory()->assignedToUser()->create();
        LicenseSeat::factory()->assignedToUser()->for($asset)->create();

        $this->assertNotNull($asset->licenseseats->first()->assigned_to);

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset));

        $this->assertNull($asset->refresh()->licenseseats->first()->assigned_to);
    }

    public function test_checking_in_asset_updates_location_of_assets_assigned_to_it()
    {
        $originalLocation = Location::factory()->create();
        $checkedOutLocation = Location::factory()->create();

        $parentAsset = Asset::factory()->assignedToLocation($checkedOutLocation)->create([
            'location_id' => $checkedOutLocation->id,
            'rtd_location_id' => $originalLocation->id,
        ]);

        $childAsset = Asset::factory()->create([
            'assigned_to' => $parentAsset->id,
            'assigned_type' => Asset::class,
            'location_id' => $checkedOutLocation->id,
            'rtd_location_id' => $originalLocation->id,
        ]);

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $parentAsset), [
                'location_id' => $originalLocation->id,
            ])
            ->assertOk();

        $this->assertEquals($originalLocation->id, $parentAsset->fresh()->location_id);
        $this->assertEquals($originalLocation->id, $childAsset->fresh()->location_id);
        $this->assertEquals($parentAsset->id, $childAsset->fresh()->assigned_to);
        $this->assertEquals(Asset::class, $childAsset->fresh()->assigned_type);
    }

    public function test_legacy_location_values_set_to_zero_are_updated()
    {
        $asset = Asset::factory()->canBeInvalidUponCreation()->assignedToUser()->create([
            'rtd_location_id' => 0,
            'location_id' => 0,
        ]);

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset));

        $this->assertNull($asset->refresh()->rtd_location_id);
        $this->assertEquals($asset->location_id, $asset->rtd_location_id);
    }

    public function test_pending_checkout_acceptances_are_cleared_upon_checkin()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $acceptance = CheckoutAcceptance::factory()->for($asset, 'checkoutable')->pending()->create();

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset));

        $this->assertFalse($acceptance->exists(), 'Acceptance was not deleted');
    }

    public function test_checkin_time_and_action_log_note_can_be_set()
    {
        Event::fake();

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', Asset::factory()->assignedToUser()->create()), [
                // time is appended to the provided date in controller
                'checkin_at' => '2023-01-02',
                'note' => 'hi there',
            ]);

        Event::assertDispatched(function (CheckoutableCheckedIn $event) {
            return Carbon::parse('2023-01-02')->isSameDay(Carbon::parse($event->action_date))
                && $event->note === 'hi there';
        }, 1);
    }
}
