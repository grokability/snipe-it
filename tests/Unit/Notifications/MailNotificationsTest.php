<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\CurrentInventory;
use App\Notifications\WelcomeNotification;
use Tests\TestCase;

class MailNotificationsTest extends TestCase
{
    public function test_welcome_notification(): void
    {
        $notification = new WelcomeNotification(User::factory()->create());

        $this->assertEquals(['mail'], $notification->via());
        $this->assertNotNull($notification->toMail());
    }

    public function test_current_inventory_notification(): void
    {
        $user = User::factory()->create();
        $notification = new CurrentInventory($user);

        $this->assertIsArray($notification->via($user));
        $this->assertNotNull($notification->toMail($user));
    }
}
