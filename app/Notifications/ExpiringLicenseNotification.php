<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class ExpiringLicenseNotification extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $params;

    /**
     * Create a new notification instance.
     *
     * @param $params
     */
    public function __construct($params, $threshold)
    {
        $this->licenses = $params;
        $this->threshold = $threshold;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
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
     * @param  mixed  $asset
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)->markdown('notifications.markdown.report-expiring-licenses',
            [
                'licenses'  => $this->licenses,
                'threshold'  => $this->threshold,
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
