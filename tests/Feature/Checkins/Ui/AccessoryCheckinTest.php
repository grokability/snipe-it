<?php

namespace Tests\Feature\Checkins\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Mail\CheckinAccessoryMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\CheckinAccessoryNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccessoryCheckinTest extends TestCase
{
    #[Test]
    public function checking_in_accessory_requires_correct_permission()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('accessories.checkin.store', $accessory->checkouts->first()->id))
            ->assertForbidden();
    }

    #[Test]
    public function page_renders()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.checkin.show', $accessory->checkouts->first()->id))
            ->assertOk();
    }

    #[Test]
    public function accessory_can_be_checked_in()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $this->assertTrue($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);

        $this->actingAs(User::factory()->checkinAccessories()->create())
            ->post(route('accessories.checkin.store', $accessory->checkouts->first()->id));

        $this->assertFalse($accessory->fresh()->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);

        Event::assertDispatched(CheckoutableCheckedIn::class, 1);
    }

    #[Test]
    public function email_sent_to_user_if_setting_enabled()
    {
        Mail::fake();

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $accessory->category->update(['checkin_email' => true]);

        event(new CheckoutableCheckedIn(
            $accessory,
            $user,
            User::factory()->checkinAccessories()->create(),
            '',
        ));
        Mail::assertSent(CheckinAccessoryMail::class, function (CheckinAccessoryMail $mail) use ($user) {
            return $mail->hasTo($user->email);

        });
    }

    #[Test]
    public function email_not_sent_to_user_if_setting_disabled()
    {
        Mail::fake();

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $accessory->category->update([
            'checkin_email' => false,
            'require_acceptance' => false,
            'eula_text' => null,
        ]);

        event(new CheckoutableCheckedIn(
            $accessory,
            $user,
            User::factory()->checkinAccessories()->create(),
            '',
        ));

        Mail::assertNotSent(CheckinAccessoryMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    #[Test]
    public function accessory_checkin_clears_pending_acceptance_when_notifications_are_fully_disabled()
    {
        $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $accessory->category->update([
            'require_acceptance' => true,
            'checkin_email' => false,
        ]);

        $acceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertTrue($acceptance->isPending());

        $this->checkInAccessoryFrom($accessory, $user);

        $this->assertAcceptanceWasSoftDeleted(
            $acceptance,
            'The pending acceptance should have been soft-deleted when the accessory was checked in but it survived.'
        );
    }

    #[Test]
    public function accessory_checkin_clears_pending_acceptance_when_only_a_webhook_is_configured()
    {
        Notification::fake();

        $this->settings->disableAdminCC()->disableAdminCCAlways()->enableSlackWebhook();

        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $accessory->category->update([
            'require_acceptance' => true,
            'checkin_email' => false,
        ]);

        $acceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertTrue($acceptance->isPending());

        $this->checkInAccessoryFrom($accessory, $user);

        $this->assertSlackNotificationSent(CheckinAccessoryNotification::class);

        $this->assertAcceptanceWasSoftDeleted(
            $acceptance,
            'The pending acceptance should have been soft-deleted when the accessory was checked in but it survived.'
        );
    }

    #[Test]
    public function accessory_checkin_does_not_clear_a_same_id_pending_acceptance_of_another_type()
    {
        Mail::fake();

        $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

        $user = User::factory()->create();

        [$accessory, $asset] = $this->createAccessoryAndAssetSharingAnId($user);

        // The asset's acceptance is created FIRST so it is the older row.
        // Accessory checkins retire only the oldest matching pending row, so
        // with the accessory's own row older this test would pass whether or
        // not the query filters on checkoutable_type — the asset's row would
        // never be reached either way.
        $assetAcceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
        ]);

        $accessoryAcceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->checkInAccessoryFrom($accessory, $user);

        $this->assertAcceptanceWasSoftDeleted(
            $accessoryAcceptance,
            'The checked-in accessory\'s own pending acceptance should have been soft-deleted.'
        );

        $this->assertNull(
            CheckoutAcceptance::withTrashed()->findOrFail($assetAcceptance->id)->deleted_at,
            'Checking in the accessory should have left the asset acceptance alone. It was soft-deleted too, because the listener matches on checkoutable_id and assigned_to_id without filtering on checkoutable_type, and the asset happens to share the accessory\'s id.'
        );
    }

    private function checkInAccessoryFrom(Accessory $accessory, User $user): void
    {
        $checkout = $accessory->checkouts()
            ->where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->firstOrFail();

        $this->actingAs(User::factory()->checkinAccessories()->create())
            ->post(route('accessories.checkin.store', $checkout->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
    }

    private function createAccessoryAndAssetSharingAnId(User $user): array
    {
        $sharedId = $this->firstIdFreeInBoth('accessories', 'assets');

        $accessory = Accessory::factory()->checkedOutToUser($user)->create(['id' => $sharedId]);
        $asset = Asset::factory()->create(['id' => $sharedId]);

        // Without the collision there is no morph bug to trigger and the test
        // passes for the wrong reason, so prove the ids really do line up.
        $this->assertSame($accessory->id, $asset->id);

        return [$accessory, $asset];
    }

    /**
     * An id no row in either table holds, so both can be created with it.
     */
    private function firstIdFreeInBoth(string $firstTable, string $secondtable): int
    {
        return max(
            (int) DB::table($firstTable)->max('id'),
            (int) DB::table($secondtable)->max('id'),
        ) + 1;
    }

    private function assertAcceptanceWasSoftDeleted(CheckoutAcceptance $acceptance, string $message): void
    {
        $this->assertNotNull(
            CheckoutAcceptance::withTrashed()->findOrFail($acceptance->id)->deleted_at,
            $message
        );
    }
}
