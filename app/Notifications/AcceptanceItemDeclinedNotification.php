<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class AcceptanceItemDeclinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $item_name;
    public $item_tag;
    public $item_model;
    public $item_serial;
    public $item_status;
    public $declined_date;
    public $note;
    public $assigned_to;
    public $company_name;
    public $qty;
    public $admin;

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
        $settings = Setting::getSettings();

        return ($settings->alerts_enabled && !empty($settings->admin_cc_email));
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        \Log::error("okay, about to mail the thing....");
        \Log::error(print_r($this, true));
        $message = (new MailMessage)->markdown('notifications.markdown.asset-acceptance',
            [
                'item_tag' => $this->item_tag,
                'item_name' => $this->item_name,
                'item_model' => $this->item_model,
                'item_serial' => $this->item_serial,
                'item_status' => $this->item_status,
                'note' => $this->note,
                'declined_date' => $this->declined_date,
                'assigned_to' => $this->assigned_to,
                'company_name' => $this->company_name,
                'qty' => $this->qty,
                'admin' => $this->admin,
                'user' => $this->assigned_to,
                'intro_text' => trans('mail.acceptance_declined_greeting', ['user' => $this->assigned_to]),
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
