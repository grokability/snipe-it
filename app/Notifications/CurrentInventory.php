<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class CurrentInventory extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Get the notification's delivery channels.
     *
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
                'licenses' => $this->user->licenses,
                'consumables' => $this->user->consumables,
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
