<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class CurrentInventory extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->indirectItemsCount = $this->user?->assets?->flatMap->assignedAssets->count() + $this->user?->assets?->flatMap->components->count() + $this->user?->assets?->flatMap->licenses->count() + $this->user?->assets?->flatMap->assignedAccessories->count();

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)->markdown('notifications.markdown.user-inventory',
            [
                'assets' => $this->user->assets,
                'accessories' => $this->user->accessories,
                'consumables' => $this->user->consumables,
                'licenses' => $this->user->directLicenses,
                'indirectItemsCount' => $this->indirectItemsCount,
            ])
            ->subject(trans('mail.inventory_report'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
