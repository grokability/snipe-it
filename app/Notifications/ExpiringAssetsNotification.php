<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class ExpiringAssetsNotification extends Notification
{
    use Queueable;

    private $params;

    /**
     * Create a new notification instance.
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
        $notifyBy = [];
        $notifyBy[] = 'mail';

        return $notifyBy;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $asset
     * @return MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)->markdown('notifications.markdown.report-expiring-assets',
            [
                'assets' => $this->assets,
                'threshold' => $this->threshold,
            ])
            ->subject('⏰'.trans('mail.Expiring_Assets_Report'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
