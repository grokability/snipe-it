<?php

namespace App\Notifications;

use App\Helpers\Helper;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcceptanceAssetAcceptedToUserNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->item_tag = $params['item_tag'];
        $this->item_model = $params['item_model'];
        $this->item_serial = $params['item_serial'];
        $this->item_status = $params['item_status'];
        $this->accepted_date = Helper::getFormattedDateObject($params['accepted_date'], 'date', false);
        $this->assigned_to = $params['assigned_to'];
        $this->note = $params['note'];
        $this->company_name = $params['company_name'];
        $this->settings = Setting::getSettings();
        $this->file = $params['file'] ?? null;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {

        $notifyBy = ['mail'];

        return $notifyBy;

    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $pdf_path = storage_path('private_uploads/eula-pdfs/'.$this->file);
        $message = (new MailMessage)->markdown('notifications.markdown.asset-acceptance',
            [
                'item_tag'      => $this->item_tag,
                'item_model'    => $this->item_model,
                'item_serial'   => $this->item_serial,
                'item_status'   => $this->item_status,
                'note'          => $this->note,
                'accepted_date' => $this->accepted_date,
                'assigned_to'   => $this->assigned_to,
                'company_name'  => $this->company_name,
                'intro_text'    => trans('mail.acceptance_asset_accepted_to_user', ['site_name' => $this->company_name ?? $this->settings->site_name]),
            ])
            ->attach($pdf_path)
            ->subject(trans('mail.acceptance_asset_accepted_to_user', ['site_name' => $this->settings->site_name]));

        return $message;
    }



}
