<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AcceptanceAssetPendingNotification extends Notification
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Notification channels
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Data stored in notifications table
     */
    public function toDatabase($notifiable)
    {
        return [
            'type'    => 'asset_acceptance',
            'title'   => 'Asset acceptance required',
            'message' => "{$this->data['assigned_to']} received asset {$this->data['item_tag']} and must accept or decline it.",
            'item_id' => $this->data['item_id'],
            'url'     => url('/account/accept'),
        ];
    }
}
