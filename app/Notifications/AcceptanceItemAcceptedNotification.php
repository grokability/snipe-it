<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class AcceptanceItemAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $item_tag;
    public $item_name;
    public $item_model;
    public $item_serial;
    public $item_status;
    public $accepted_date;
    public $assigned_to;
    public $company_name;
    public $file;
    public $qty;
    public $note;
    public $custom_fields;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->item_tag = $params['item_tag'];
        $this->item_name = $params['item_name'];
        $this->item_model = $params['item_model'];
        $this->item_serial = $params['item_serial'];
        $this->item_status = $params['item_status'];
        $this->accepted_date = $params['accepted_date'];
        $this->assigned_to = $params['assigned_to'];
        $this->company_name = $params['company_name'];
        $this->file = $params['file'] ?? null;
        $this->qty = $params['qty'] ?? null;
        $this->note = $params['note'] ?? null;
        $this->custom_fields = $params['custom_fields'] ?? [];

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

    public function shouldSend($notifiable, $channel)
    {
        $settings = Setting::getSettings();

        return $settings->alerts_enabled && (!empty($settings->alert_email) || !empty($settings->admin_cc_email));
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)->markdown('notifications.markdown.asset-acceptance',
            [
                'item_tag' => $this->item_tag,
                'item_name' => $this->item_name,
                'item_model' => $this->item_model,
                'item_serial' => $this->item_serial,
                'item_status' => $this->item_status,
                'note' => $this->note,
                'accepted_date' => $this->accepted_date,
                'assigned_to' => $this->assigned_to,
                'company_name' => $this->company_name,
                'qty' => $this->qty,
                'custom_fields' => $this->custom_fields,
                'intro_text' => trans('mail.acceptance_accepted_greeting', ['user' => $this->assigned_to, 'item' => $this->item_name]),
            ])
            ->subject('✅ '.trans('mail.acceptance_accepted', ['user' => $this->assigned_to, 'item' => $this->item_name]))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
