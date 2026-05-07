<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class ExpectedCheckinAdminNotification extends Notification
{
    use Queueable;

    private $params;

    /**
     * Create a new notification instance.
     */
    public function __construct($params)
    {
        $this->assets = $params;
    }

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
        $message = (new MailMessage)->markdown('notifications.markdown.report-expected-checkins',
            [
                'assets' => $this->assets,
            ])
            ->subject('⏰'.trans('mail.Expected_Checkin_Report'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
