<?php

namespace Tests\Support;

use App\Models\Setting;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

trait AssertsAgainstSlackNotifications
{
    public function assertSlackNotificationSent(string $notificationClass)
    {
        Notification::assertSentTo(
            new AnonymousNotifiable,
            $notificationClass,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['slack'] === Setting::getSettings()->webhook_endpoint;
            }
        );
    }

    public function assertNoSlackNotificationSent(string $notificationClass)
    {
        Notification::assertNotSentTo(new AnonymousNotifiable, $notificationClass);
    }
}
