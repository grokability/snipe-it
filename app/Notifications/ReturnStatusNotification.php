<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReturnStatusNotification extends Notification
{
    use Queueable;

    public string $event; // requested | in_transit | received
    public $return;       // ReturnRequest
    public $asset;        // Asset
    public $actor;        // User

    public function __construct(string $event, $return, $asset = null, $actor = null)
    {
        $this->event  = $event;
        $this->return = $return;
        $this->asset  = $asset ?: ($return->asset ?? null);
        $this->actor  = $actor;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $assetName = $this->asset?->name ?? 'Asset';
        $assetTag  = $this->asset?->asset_tag ?? ($this->asset?->id ?? '');
        $who       = $this->actor?->display_name ?? $this->actor?->username ?? 'System';

        $type = match ($this->event) {
            'requested'  => 'return_requested',
            'in_transit' => 'return_in_transit',
            'received'   => 'return_received',
            default      => 'return_update',
        };

        $title = match ($this->event) {
            'requested'  => 'Return to Archive',
            'in_transit' => 'Marked In Transit',
            'received'   => 'Marked Received',
            default      => 'Return update',
        };

        $message = match ($this->event) {
            'requested'  => "Return requested for {$assetName} ({$assetTag}) by {$who}.",
            'in_transit' => "Return marked In Transit for {$assetName} ({$assetTag}) by {$who}.",
            'received'   => "Warehouse received {$assetName} ({$assetTag}) (by {$who}).",
            default      => "Return updated for {$assetName} ({$assetTag}) by {$who}.",
        };

        $url = ($this->event === 'received')
            ? '/hardware/' . ($this->asset?->id)
            : '/returns';

        return [
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'url'       => $url,
            'event'     => $this->event,
            'return_id' => $this->return?->id,
            'asset_id'  => $this->asset?->id,
        ];
    }
}
