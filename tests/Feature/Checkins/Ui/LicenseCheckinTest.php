<?php

namespace Tests\Feature\Checkins\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckinLicenseSeatNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseCheckinTest extends TestCase
{
    #[Test]
    public function checking_in_license_requires_correct_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('licenses.checkin.save', [
                'licenseId' => LicenseSeat::factory()->assignedToUser()->create()->id,
            ]))
            ->assertForbidden();
    }

    #[Test]
    public function non_reassignable_seat_remains_unreassignable_after_checkin()
    {
        $licenseSeat = LicenseSeat::factory()
            ->notReassignable()
            ->assignedToUser()
            ->create();

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.checkin.save', $licenseSeat));

        $licenseSeat->refresh();

        $this->assertEquals(true, $licenseSeat->unreassignable_seat);
    }

    #[Test]
    public function cannot_checkin_license_that_is_not_assigned()
    {
        $licenseSeat = LicenseSeat::factory()
            ->reassignable()
            ->create();

        $this->assertNull($licenseSeat->assigned_to);
        $this->assertNull($licenseSeat->asset_id);

        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.checkin.save', $licenseSeat), [
                'notes' => 'my note',
                'redirect_option' => 'index',
            ])
            ->assertSessionHas('error', trans('admin/licenses/message.checkin.error'));
    }

    #[Test]
    public function can_check_in_license_assigned_to_asset()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $asset = Asset::factory()->create();

        $licenseSeat = LicenseSeat::factory()
            ->reassignable()
            ->assignedToAsset($asset)
            ->create();

        $actor = User::factory()->checkinLicenses()->create();

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

    #[Test]
    public function can_check_in_license_assigned_to_user()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();

        $licenseSeat = LicenseSeat::factory()
            ->reassignable()
            ->assignedToUser($user)
            ->create();

        $actor = User::factory()->checkinLicenses()->create();

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

    #[Test]
    public function page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.checkin', LicenseSeat::factory()->assignedToUser()->create()->id))
            ->assertOk();

    }

    #[Test]
    public function license_seat_checkin_clears_pending_acceptance_when_notifications_are_fully_disabled()
    {
        $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

        $user = User::factory()->create();
        $seat = $this->seatAssignedTo($user);

        $acceptance = $this->pendingSeatAcceptance($seat, $user);

        $this->assertNoNotificationChannelIsConfigured($seat);

        $this->checkInSeat($seat);

        $this->assertAcceptanceWasSoftDeleted(
            $acceptance,
            'The pending acceptance should have been soft-deleted when the seat was checked in, but it survived: with no email and no webhook configured the listener returns before it reaches the delete.'
        );
    }

    #[Test]
    public function license_seat_checkin_clears_pending_acceptance_when_only_a_webhook_is_configured()
    {
        Notification::fake();

        $this->settings->disableAdminCC()->disableAdminCCAlways()->enableSlackWebhook();

        $user = User::factory()->create();
        $seat = $this->seatAssignedTo($user);

        $acceptance = $this->pendingSeatAcceptance($seat, $user);

        $this->checkInSeat($seat);

        $this->assertSlackNotificationSent(CheckinLicenseSeatNotification::class);

        $this->assertAcceptanceWasSoftDeleted(
            $acceptance,
            'The pending acceptance should have been soft-deleted when the seat was checked in, but it survived: a webhook-only install never enters the notification branch that holds the delete.'
        );
    }

    #[Test]
    public function license_seat_checkin_does_not_clear_a_same_id_pending_acceptance_of_another_type()
    {
        $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

        $user = User::factory()->create();

        [$seat, $asset] = $this->createSeatAndAssetSharingAnId($user);

        $seatAcceptance = $this->pendingSeatAcceptance($seat, $user);

        $assetAcceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
        ]);
        $this->assertSame(Asset::class, $assetAcceptance->checkoutable_type);

        $this->checkInSeat($seat);

        $this->assertAcceptanceWasSoftDeleted(
            $seatAcceptance,
            'The checked-in seat\'s own pending acceptance should have been soft-deleted.'
        );

        $this->assertNull(
            CheckoutAcceptance::withTrashed()->findOrFail($assetAcceptance->id)->deleted_at,
            'Checking in the license seat should have left the asset acceptance alone. It was soft-deleted too, because the listener matches on checkoutable_id and assigned_to_id without filtering on checkoutable_type, and the asset happens to share the seat\'s id.'
        );
    }

    /**
     * Acceptance required, checkin email off — the configuration where a stale
     * pending acceptance can outlive the assignment it belongs to.
     */
    private function licenseRequiringAcceptanceWithoutCheckinEmail(): License
    {
        return License::factory()->create([
            'reassignable' => 1,
            'category_id' => Category::factory()->create([
                'category_type' => 'license',
                'require_acceptance' => 1,
                'checkin_email' => 0,
            ])->id,
        ]);
    }

    private function seatAssignedTo(User $user): LicenseSeat
    {
        return LicenseSeat::factory()
            ->assignedToUser($user)
            ->create(['license_id' => $this->licenseRequiringAcceptanceWithoutCheckinEmail()->id]);
    }

    /**
     * A real seat acceptance is keyed to the SEAT — LicenseSeat::class plus the
     * seat's own id — which is what CreateCheckoutAcceptanceAction writes. There
     * is no morph map aliasing License and LicenseSeat.
     */
    private function pendingSeatAcceptance(LicenseSeat $seat, User $user): CheckoutAcceptance
    {
        $acceptance = CheckoutAcceptance::factory()
            ->forLicenseSeat()
            ->pending()
            ->create([
                'checkoutable_id' => $seat->id,
                'assigned_to_id' => $user->id,
            ]);

        $this->assertSame(LicenseSeat::class, $acceptance->checkoutable_type);
        $this->assertTrue($acceptance->isPending());

        return $acceptance;
    }

    private function checkInSeat(LicenseSeat $seat): void
    {
        $this->actingAs(User::factory()->checkinLicenses()->create())
            ->post(route('licenses.checkin.save', $seat->id))
            ->assertRedirect();

        // Confirm the checkin actually happened before asserting anything about
        // what it did or didn't clean up.
        $this->assertNull($seat->refresh()->assigned_to, 'The seat was not checked in, so nothing downstream ran.');
    }

    private function createSeatAndAssetSharingAnId(User $user): array
    {
        // A license spawns its own seats when it saves, so the id is only free
        // to claim once those exist.
        $license = $this->licenseRequiringAcceptanceWithoutCheckinEmail();

        $sharedId = $this->firstIdFreeInBoth('license_seats', 'assets');

        $seat = LicenseSeat::factory()
            ->assignedToUser($user)
            ->create(['license_id' => $license->id, 'id' => $sharedId]);

        $asset = Asset::factory()->create(['id' => $sharedId]);

        // Without the collision there is no morph bug to trigger and the test
        // passes for the wrong reason, so prove the ids really do line up.
        $this->assertSame($seat->id, $asset->id);

        return [$seat, $asset];
    }

    /**
     * An id no row in either table holds, so both can be created with it.
     * Taken above both maxima rather than read from the auto-increment
     * counters, which are engine-specific to query.
     */
    private function firstIdFreeInBoth(string $table, string $otherTable): int
    {
        return max(
            (int) DB::table($table)->max('id'),
            (int) DB::table($otherTable)->max('id'),
        ) + 1;
    }

    private function assertNoNotificationChannelIsConfigured(LicenseSeat $seat): void
    {
        $this->assertFalse((bool) $seat->license->checkin_email());
        $this->assertEmpty(Setting::getSettings()->admin_cc_email);
        $this->assertEmpty(Setting::getSettings()->webhook_endpoint);
    }

    private function assertAcceptanceWasSoftDeleted(CheckoutAcceptance $acceptance, string $message): void
    {
        $this->assertNotNull(
            CheckoutAcceptance::withTrashed()->findOrFail($acceptance->id)->deleted_at,
            $message
        );
    }
}
