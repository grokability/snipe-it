<?php

namespace App\Custom\Notifications;

use App\Custom\Models\SparePart;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected SparePart $sparePart,
        protected int $currentQty,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $part = $this->sparePart;
        $suggestedOrder = max(0, ($part->minimum_quantity * 2) - $this->currentQty);

        $leadTimeText = $part->lead_time_days
            ? "{$part->lead_time_days} " . __('custom::fom.lbl_days')
            : __('custom::fom.notif_low_stock_not_set');

        return (new MailMessage)
            ->subject(__('custom::fom.notif_low_stock_subject', [
                'name' => $part->name,
                'qty'  => $this->currentQty,
            ]))
            ->greeting(__('custom::fom.notif_low_stock_greeting'))
            ->line(__('custom::fom.notif_low_stock_part', [
                'name'   => $part->name,
                'number' => $part->part_number,
            ]))
            ->line(__('custom::fom.notif_low_stock_current', ['qty' => $this->currentQty]))
            ->line(__('custom::fom.notif_low_stock_minimum', ['min' => $part->minimum_quantity]))
            ->line(__('custom::fom.notif_low_stock_lead', ['lead' => $leadTimeText]))
            ->line(__('custom::fom.notif_low_stock_suggested', ['qty' => $suggestedOrder]))
            ->action(__('custom::fom.notif_low_stock_action'), url("/fom/spare-parts/{$part->id}"))
            ->salutation(__('custom::fom.notif_salutation'));
    }

    public function toArray(object $notifiable): array
    {
        $part = $this->sparePart;

        return [
            'spare_part_id'    => $part->id,
            'part_name'        => $part->name,
            'part_number'      => $part->part_number,
            'current_qty'      => $this->currentQty,
            'minimum_qty'      => $part->minimum_quantity,
            'lead_time_days'   => $part->lead_time_days,
            'suggested_order'  => max(0, ($part->minimum_quantity * 2) - $this->currentQty),
        ];
    }
}
