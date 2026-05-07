<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class SendUpcomingAuditNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($params, $threshold)
    {
        $this->assets = $params;
        $this->threshold = $threshold;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return $notifyBy = ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)->markdown('notifications.markdown.upcoming-audits',
            [
                'assets' => $this->assets,
                'threshold' => $this->threshold,
            ])
            ->subject('⏰'.trans_choice('mail.upcoming-audits', $this->assets->count(), ['count' => $this->assets->count(), 'threshold' => $this->threshold]))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
