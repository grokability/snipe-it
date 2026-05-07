<?php

namespace Tests\Feature\Checkins\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Mail\CheckinAccessoryMail;
use App\Models\Accessory;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccessoryCheckinTest extends TestCase
{
    public function test_checking_in_accessory_requires_correct_permission()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('accessories.checkin.store', $accessory->checkouts->first()->id))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.checkin.show', $accessory->checkouts->first()->id))
            ->assertOk();
    }

    public function test_accessory_can_be_checked_in()
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

    public function test_email_sent_to_user_if_setting_enabled()
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

    public function test_email_not_sent_to_user_if_setting_disabled()
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
}
