<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class ExpiringLicenseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $params,
        public $threshold
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        $notifyBy = [];
        $notifyBy[] = 'mail';

        return $notifyBy;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)->markdown('notifications.markdown.report-expiring-licenses',
            [
                'licenses' => $this->licenses,
                'threshold' => $this->threshold,
            ])
            ->subject('⏰'.trans('mail.Expiring_Licenses_Report'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
