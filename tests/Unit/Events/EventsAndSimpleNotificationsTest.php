<?php

namespace Tests\Unit\Events;

use App\Events\CheckoutableCheckedOut;
use App\Events\CheckoutAccepted;
use App\Events\CheckoutablesCheckedOutInBulk;
use App\Events\CheckoutDeclined;
use App\Events\UserMerged;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\FirstAdminNotification;
use App\Notifications\MailTest;
use App\Notifications\SendUpcomingAuditNotification;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EventsAndSimpleNotificationsTest extends TestCase
{
    public function test_checkout_acceptance_events(): void
    {
        $acceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->assertInstanceOf(CheckoutAccepted::class, new CheckoutAccepted($acceptance));
        $this->assertInstanceOf(CheckoutDeclined::class, new CheckoutDeclined($acceptance));
    }

    public function test_user_merged_event(): void
    {
        $event = new UserMerged(User::factory()->create(), User::factory()->create(), User::factory()->create());

        $this->assertInstanceOf(UserMerged::class, $event);
    }

    public function test_bulk_checkout_event(): void
    {
        $event = new CheckoutablesCheckedOutInBulk(
            new Collection([Asset::factory()->create()]),
            User::factory()->create(),
            User::factory()->create(),
            now()->toDateTimeString(),
            '',
            'note'
        );

        $this->assertInstanceOf(CheckoutablesCheckedOutInBulk::class, $event);
    }

    public function test_mail_test_notification(): void
    {
        $n = new MailTest;

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toMail());
    }

    public function test_first_admin_notification(): void
    {
        $n = new FirstAdminNotification([
            'first_name' => 'Jane', 'last_name' => 'Doe',
            'email' => 'jane@example.com', 'username' => 'jdoe', 'password' => 'secret',
        ]);

        $this->assertEquals(['mail'], $n->via());
        $this->assertNotNull($n->toMail());
    }

    public function test_send_upcoming_audit_notification(): void
    {
        $n = new SendUpcomingAuditNotification(new Collection, 30);

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toMail());
    }
}
