<?php

namespace Tests\Unit\Models;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssetCheckInTest extends TestCase
{
    public function test_disassociates_the_assignee()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $this->assertTrue($asset->checkIn(User::factory()->create()));

        $fresh = $asset->fresh();
        $this->assertNull($fresh->assigned_to);
        $this->assertNull($fresh->assigned_type);
    }

    public function test_clears_expected_checkin_and_sets_last_checkin()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'expected_checkin' => now()->addDays(7),
        ]);

        $asset->checkIn(User::factory()->create());

        $fresh = $asset->fresh();
        $this->assertNull($fresh->expected_checkin);
        $this->assertNotNull($fresh->last_checkin);
    }

    public function test_clears_accepted_flag()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create(['accepted' => 'accepted']);

        $asset->checkIn(User::factory()->create());

        $this->assertNull($asset->fresh()->accepted);
    }

    public function test_resets_location_to_rtd_location_by_default()
    {
        $rtdLocation = Location::factory()->create();
        $elsewhere = Location::factory()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'rtd_location_id' => $rtdLocation->id,
            'location_id' => $elsewhere->id,
        ]);

        $asset->checkIn(User::factory()->create());

        $this->assertSame($rtdLocation->id, $asset->fresh()->location_id);
    }

    public function test_explicit_location_override_wins_over_rtd_reset()
    {
        $rtdLocation = Location::factory()->create();
        $override = Location::factory()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $asset->checkIn(User::factory()->create(), null, null, $override->id);

        $this->assertSame($override->id, $asset->fresh()->location_id);
    }

    public function test_update_default_location_true_rewrites_rtd_too()
    {
        $rtdLocation = Location::factory()->create();
        $override = Location::factory()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $asset->checkIn(User::factory()->create(), null, null, $override->id, true);

        $fresh = $asset->fresh();
        $this->assertSame($override->id, $fresh->location_id);
        $this->assertSame($override->id, $fresh->rtd_location_id);
    }

    public function test_releases_all_assigned_license_seats()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();
        $license = License::factory()->create();
        $seatA = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => $asset->id,
            'assigned_to' => $user->id,
        ]);
        $seatB = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => $asset->id,
            'assigned_to' => $user->id,
        ]);

        $asset->checkIn(User::factory()->create());

        $this->assertNull($seatA->fresh()->assigned_to);
        $this->assertNull($seatB->fresh()->assigned_to);
    }

    public function test_deletes_pending_acceptances()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
        ]);

        $asset->checkIn(User::factory()->create());

        $this->assertNull(CheckoutAcceptance::find($acceptance->id));
    }

    public function test_fires_checked_in_event_with_target_and_actor()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $target = User::factory()->create();
        $actor = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($target)->create();

        $asset->checkIn($actor, 'custom note');

        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($asset, $target, $actor) {
            return $event->checkoutable->id === $asset->id
                && $event->checkedOutTo->id === $target->id
                && $event->checkedInBy->id === $actor->id
                && $event->note === 'custom note';
        });
    }

    public function test_actor_defaults_to_authenticated_user_when_null()
    {
        Event::fake([CheckoutableCheckedIn::class]);
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $target = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($target)->create();

        $asset->checkIn();

        Event::assertDispatched(CheckoutableCheckedIn::class, fn ($e) => $e->checkedInBy->id === $actor->id);
    }
}
