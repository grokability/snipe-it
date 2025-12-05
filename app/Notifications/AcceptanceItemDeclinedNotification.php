<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class AcceptanceItemDeclinedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->item_name = $params['item_name'];
        $this->item_tag = $params['item_tag'];
        $this->item_model = $params['item_model'];
        $this->item_serial = $params['item_serial'];
        $this->item_status = $params['item_status'];
        $this->declined_date = $params['declined_date'];
        $this->note = $params['note'];
        $this->assigned_to = $params['assigned_to'];
        $this->company_name = $params['company_name'];
        $this->settings = Setting::getSettings();
        $this->qty = $params['qty'] ?? null;
        $this->admin = $params['admin'] ?? null;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $notifyBy = ['mail'];

        return $notifyBy;

    }

    public function shouldSend($notifiable, $channel)
    {
        return $this->settings->alerts_enabled && ! empty($this->settings->alert_email);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)->markdown('notifications.markdown.asset-acceptance',
            [
                'item_tag'      => $this->item_tag,
                'item_name'     => $this->item_name,
                'item_model'    => $this->item_model,
                'item_serial'   => $this->item_serial,
                'item_status'   => $this->item_status,
                'note'          => $this->note,
                'declined_date' => $this->declined_date,
                'assigned_to'   => $this->assigned_to,
                'company_name'  => $this->company_name,
                'qty'           => $this->qty,
                'admin'         => $this->admin,
                'user'          => $this->assigned_to,
                'intro_text'    => trans('mail.acceptance_declined_greeting', ['user' => $this->assigned_to]),
            ])
            ->subject('⚠️ '.trans('mail.acceptance_declined', ['user' => $this->assigned_to, 'item' => $this->item_name]))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }

}
