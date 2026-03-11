<?php

namespace App\Custom\Notifications;

use App\Custom\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewWorkOrderNotification extends Notification
{
    use Queueable;

    public function __construct(protected WorkOrder $workOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $wo = $this->workOrder;

        $priorityLabel = match ($wo->priority) {
            'kritik' => __('custom::fom.priority_kritik_upper'),
            'yuksek' => __('custom::fom.priority_yuksek_upper'),
            default  => __('custom::fom.priority_normal'),
        };

        return (new MailMessage)
            ->subject(__('custom::fom.notif_new_wo_subject', [
                'number'   => $wo->work_order_number,
                'priority' => $priorityLabel,
            ]))
            ->greeting(__('custom::fom.notif_new_wo_greeting'))
            ->line(__('custom::fom.notif_new_wo_number', ['number' => $wo->work_order_number]))
            ->line(__('custom::fom.notif_new_wo_priority', ['priority' => $priorityLabel]))
            ->line(__('custom::fom.notif_new_wo_asset', ['asset' => $wo->asset?->name ?? __('custom::fom.lbl_dash')]))
            ->line(__('custom::fom.notif_new_wo_location', ['location' => $wo->location?->name ?? __('custom::fom.lbl_dash')]))
            ->line(__('custom::fom.notif_new_wo_description', ['description' => $wo->description]))
            ->line(__('custom::fom.notif_new_wo_reporter', ['reporter' => $wo->reportedBy?->full_name ?? __('custom::fom.lbl_dash')]))
            ->action(__('custom::fom.notif_new_wo_action'), url("/fom/work-orders/{$wo->id}"))
            ->salutation(__('custom::fom.notif_salutation'));
    }

    public function toArray(object $notifiable): array
    {
        $wo = $this->workOrder;

        return [
            'work_order_id'     => $wo->id,
            'work_order_number' => $wo->work_order_number,
            'priority'          => $wo->priority,
            'asset_name'        => $wo->asset?->name,
            'description'       => $wo->description,
            'reported_by'       => $wo->reportedBy?->full_name,
        ];
    }
}
