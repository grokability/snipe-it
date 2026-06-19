<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedToUserNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

/**
 * Cubre AcceptanceItemAcceptedToUserNotification (antes 0%): via() y toMail().
 */
class AcceptanceItemAcceptedToUserNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function params(): array
    {
        return [
            'item_tag' => 'A-100',
            'item_name' => 'Laptop',
            'item_model' => 'XPS',
            'item_serial' => 'SN1',
            'item_status' => 'Deployed',
            'accepted_date' => now(),
            'assigned_to' => 'Jane Doe',
            'note' => 'aceptado',
            'company_name' => 'Acme',
            'file' => 'eula.pdf',
            'qty' => 1,
            'custom_fields' => [],
        ];
    }

    public function test_via_is_mail(): void
    {
        $notification = new AcceptanceItemAcceptedToUserNotification($this->params());

        $this->assertSame(['mail'], $notification->via());
    }

    public function test_to_mail_builds_message(): void
    {
        $notification = new AcceptanceItemAcceptedToUserNotification($this->params());

        $this->assertInstanceOf(MailMessage::class, $notification->toMail());
    }
}
