<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AssetRequestCanceledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public array $payload
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'type'    => 'asset_request_canceled',
            'title'   => $this->payload['title'] ?? 'Request canceled',
            'message' => $this->payload['message'] ?? '',
            'url'     => $this->payload['url'] ?? '/account/requested',
            'item_id' => $this->payload['item_id'] ?? null,
        ]);
    }
}
