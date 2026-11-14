<?php

namespace App\Notifications;

use App\Helpers\Helper;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class RequestAssetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $target;

    public $item;

    public $item_type;

    public $item_quantity;

    public $note = '';

    public $last_checkout = '';

    public $expected_checkin = '';

    public $requested_date;

    public $start_date;

    public $end_date;

    /**
     * Create a new notification instance.
     */
    public function __construct($params)
    {
        $this->target = $params['target'];
        $this->item = $params['item'];
        $this->item_type = $params['item_type'];
        $this->item_quantity = $params['item_quantity'];
        $this->requested_date = Helper::getFormattedDateObject($params['requested_date'], 'datetime',
            false);

        if (array_key_exists('note', $params)) {
            $this->note = $params['note'];
        }

        if ($this->item->last_checkout) {
            $this->last_checkout = Helper::getFormattedDateObject($this->item->last_checkout, 'date',
                false);
        }

        if ($this->item->expected_checkin) {
            $this->expected_checkin = Helper::getFormattedDateObject($this->item->expected_checkin, 'date',
                false);
        }

        // Optional reservation window. Passed in from callers that
        // capture the requester's date-range picker input (see
        // ViewAssetsController::getRequestItem). Rendered by the
        // markdown template only when set, so unspecified stays out
        // of the mail entirely.
        $this->start_date = ! empty($params['start_date'])
            ? Helper::getFormattedDateObject($params['start_date'], 'date', false)
            : '';
        $this->end_date = ! empty($params['end_date'])
            ? Helper::getFormattedDateObject($params['end_date'], 'date', false)
            : '';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        $notifyBy = [];

        if (Setting::getSettings()->webhook_endpoint != '') {
            $notifyBy[] = 'slack';
        }

        $notifyBy[] = 'mail';

        return $notifyBy;
    }

    public function toSlack()
    {
        $target = $this->target;
        $qty = $this->item_quantity;
        $item = $this->item;
        $note = $this->note;
        $botname = (Setting::getSettings()->webhook_botname) ? Setting::getSettings()->webhook_botname : 'Snipe-Bot';
        $channel = (Setting::getSettings()->webhook_channel) ? Setting::getSettings()->webhook_channel : '';

        $fields = [
            'QTY' => $qty,
            'Requested By' => '<'.$target->present()->viewUrl().'|'.$target->display_name.'>',
        ];

        if ($this->start_date) {
            $fields['Start Date'] = $this->start_date;
        }
        if ($this->end_date) {
            $fields['End Date'] = $this->end_date;
        }

        return (new SlackMessage)
            ->content(trans('mail.Item_Requested'))
            ->from($botname)
            ->to($channel)
            ->attachment(function ($attachment) use ($item, $note, $fields) {
                $attachment->title(htmlspecialchars_decode($item->display_name), $item->present()->viewUrl())
                    ->fields($fields)
                    ->content($note);
            });
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail()
    {
        $fields = [];

        // Check if the item has custom fields associated with it
        if (($this->item->custom_fieldset)) {
            $fields = $this->item->custom_fieldset->fields;
        }

        $message = (new MailMessage)->markdown('notifications.markdown.asset-requested',
            [
                'item' => $this->item,
                'note' => $this->note,
                'requested_by' => $this->target,
                'requested_date' => $this->requested_date,
                'fields' => $fields,
                'last_checkout' => $this->last_checkout,
                'expected_checkin' => $this->expected_checkin,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'intro_text' => trans('mail.a_user_requested'),
                'qty' => $this->item_quantity,
            ])
            ->subject('👀 '.trans('mail.Item_Requested'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
