<?php

namespace App\Notifications;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class ExpectedCheckinNotification extends Notification
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
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        $notifyBy = [];
        $item = $this->params['item'];

        $notifyBy[] = 'mail';

        return $notifyBy;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $today    = Carbon::today();
        $expected = Carbon::parse($this->params->expected_checkin)->startOfDay();

        $subjectText = $today->greaterThan($expected)
            ? trans('mail.Expected_Checkin_Notification_Pastdue', ['name' => $this->params->display_name])
            : trans('mail.Expected_Checkin_Notification', ['name' => $this->params->display_name]);

        $message = (new MailMessage)->markdown('notifications.markdown.expected-checkin',
            [
                'expected_checkin_date' => $this->params->expected_checkin,
                'date' => Helper::getFormattedDateObject($this->params->expected_checkin, 'date', false),
                'asset' => $this->params->display_name,
                'serial' => $this->params->serial,
                'asset_tag' => $this->params->asset_tag,
            ])
            ->subject('⏰'. $subjectText)
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
