<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcceptanceApprovalRequiredNotification extends Notification
{
    use Queueable;

    public function __construct(public array $data) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'acceptance_required',
            'title' => 'Acceptance required',
            'message' => $this->data['message'] ?? 'You have received an asset. Please Accept or Decline it.',
            'item_tag' => $this->data['item_tag'] ?? null,
            'item_name' => $this->data['item_name'] ?? null,
            'url' => url('/account/accept'),
        ];
    }
}
