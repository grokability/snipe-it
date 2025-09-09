<?php

namespace App\Notifications;

use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        $message = (new MailMessage)->markdown('notifications.markdown.expected-checkin',
            [
                'date' => Helper::getFormattedDateObject($this->params->expected_checkin, 'date', false),
                'asset' => $this->params->display_name,
                'serial' => $this->params->serial,
                'asset_tag' => $this->params->asset_tag,
            ])
            ->subject(trans('mail.Expected_Checkin_Notification', ['name' => $this->params->display_name]));

        return $message;
    }
}
