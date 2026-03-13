<?php

namespace Tests\Feature\Checkins\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LicenseCheckinTest extends TestCase
{
    public function testCheckingInLicenseRequiresCorrectPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('licenses.checkin.save', [
                'licenseId' => LicenseSeat::factory()->assignedToUser()->create()->id,
            ]))
            ->assertForbidden();
    }

    public function testNonReassignableLicenseSeatCantBeCheckedOut()
    {
        $licenseSeat = LicenseSeat::factory()
            ->notReassignable()
            ->assignedToUser()
            ->create();

        $this->actingAs(User::factory()->checkoutLicenses()->create())
            ->post(route('licenses.checkin.save', $licenseSeat));

        $licenseSeat->refresh();

        $this->assertEquals(true, $licenseSeat->unreassignable_seat);
    }

    public function testCannotCheckinLicenseThatIsNotAssigned()
    {
        $licenseSeat = LicenseSeat::factory()
            ->reassignable()
            ->create();

        $this->assertNull($licenseSeat->assigned_to);
        $this->assertNull($licenseSeat->asset_id);

        $this->actingAs(User::factory()->checkoutLicenses()->create())
            ->post(route('licenses.checkin.save', $licenseSeat), [
                'notes' => 'my note',
                'redirect_option' => 'index',
            ])
            ->assertSessionHas('error', trans('admin/licenses/message.checkin.error'));
    }

    public function testCanCheckInLicenseAssignedToAsset()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $asset = Asset::factory()->create();

        $licenseSeat = LicenseSeat::factory()
            ->reassignable()
            ->assignedToAsset($asset)
            ->create();

        $actor = User::factory()->checkoutLicenses()->create();

        $this->actingAs($actor)
            ->post(route('licenses.checkin.save', $licenseSeat), [
                'notes' => 'my note',
                'redirect_option' => 'index',
            ])
            ->assertRedirect(route('licenses.index'));

        $this->assertNull($licenseSeat->fresh()->asset_id);
        $this->assertNull($licenseSeat->fresh()->assigned_to);
        $this->assertEquals('my note', $licenseSeat->fresh()->notes);

        Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($actor, $asset, $licenseSeat) {
            return $event->checkoutable->is($licenseSeat)
                && $event->checkedOutTo->is($asset)
                && $event->checkedInBy->is($actor)
                && $event->note === 'my note';
        });
    }

    public function testCanCheckInLicenseAssignedToUser()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();

        $licenseSeat = LicenseSeat::factory()
            ->reassignable()
            ->assignedToUser($user)
            ->create();

        $actor = User::factory()->checkoutLicenses()->create();

        $this->actingAs($actor)
            ->post(route('licenses.checkin.save', $licenseSeat), [
                'notes' => 'my note',
                'redirect_option' => 'index',
            ])
            ->assertRedirect(route('licenses.index'));

        $this->assertNull($licenseSeat->fresh()->asset_id);
        $this->assertNull($licenseSeat->fresh()->assigned_to);
        $this->assertEquals('my note', $licenseSeat->fresh()->notes);

        Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($actor, $licenseSeat, $user) {
            return $event->checkoutable->is($licenseSeat)
                && $event->checkedOutTo->is($user)
                && $event->checkedInBy->is($actor)
                && $event->note === 'my note';
        });

    }
  
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.checkin', LicenseSeat::factory()->assignedToUser()->create()->id))
            ->assertOk();

    }
}
