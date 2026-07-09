<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

/**
 * Live (in-page) notifications component.
 *
 * Supports:
 * - Dispatching events with basic (type, message)
 * - Extended payload (type, title, message, description, icon, html, confetti, auto, tag)
 * - Replacing an existing alert by tag (so you can "update progress" style messages)
 * - Dismissing by id or by tag
 * - Optional legacy string-only usage
 *
 * JS Examples (Livewire v3):
 *
 * Livewire.dispatch('showNotification', {
 *   type: 'success',
 *   title: 'Asset Saved',
 *   message: 'MacBook Pro added.',
 *   description: 'Tag: MBP-4418<br>Assigned to Jane Doe',
 *   icon: 'fas fa-laptop',
 *   html: true,
 *   tag: 'asset-create'
 * });
 *
 * Update same tagged notification later:
 * Livewire.dispatch('showNotification', {
 *   type: 'info',
 *   message: 'Processing (70%)',
 *   tag: 'bulk-import'
 * });
 *
 * Dismiss all notifcations:
 * Livewire.dispatch('dismissAllNotifications');
 * 
 */
class Notifications extends Component
{
    /**
     * Each alert structure:
     * [
     *   'id'          => string,
     *   'type'        => 'success'|'danger'|'warning'|'info',
     *   'tag'         => string|null,
     *   'title'       => string|null,
     *   'message'     => string,
     *   'description' => string|null,
     *   'icon'        => string|null,
     *   'html'        => bool,
     *   'created_at'  => int         (timestamp)
     * ]
     *
     * @var array<int, array<string,mixed>>
     */

    public array $liveAlerts=[];

    /**
     * Main notification listener.
     * We bind both 'showNotification' (your current event) and 'notify' (optional alias).
     */
    
    #[On('showNotification')]
    #[On('notify')]
    public function notify(
        $type=null,
        $message=null,
        $title=null,
        $description=null,
        $icon=null,
        $html=null,
        $tag=null,
        $payload=null // wrapper form: { payload: { ... } }
    ): void {
        // Wrapper form: { payload: { ...full data... } }
        if (is_array($payload)) {
            $this->ingestArray($payload);
            return;
        }

        // Legacy simple usage: Livewire.dispatch('showNotification', 'Quick saved!')
        if (is_string($type) && $message === null && $title === null) {
            $this->pushAlert([
                'type'    => 'success',
                'message' => $type,
                'tag'     => $tag,
            ]);
            return;
        }

        // Must have a type + message at minimum
        if (!$type || !$message) {
            return;
        }

        $this->pushAlert([
            'type'        => $type,
            'message'     => $message,
            'title'       => $title,
            'description' => $description,
            'icon'        => $icon,
            'html'        => (bool) $html,
            'tag'         => $tag,
        ]);
    }

    /**
     * Ingest an associative array payload (supports multiple key name variants).
     */
    protected function ingestArray(array $arr): void
    {
        $this->pushAlert([
            'type'        => $arr['type']        ?? $arr['level'] ?? 'info',
            'message'     => $arr['message']     ?? $arr['msg'] ?? null,
            'title'       => $arr['title']       ?? $arr['heading'] ?? null,
            'description' => $arr['description'] ?? $arr['desc'] ?? null,
            'icon'        => $arr['icon']        ?? null,
            'html'        => (bool) ($arr['html'] ?? false),
            'tag'         => $arr['tag'] ?? null,
        ]);
    }

    /**
     * Normalize a semantic type into a Bootstrap alert class fragment.
     */
    protected function normalizeType(string $type): string
    {
        return match (strtolower($type)) {
            'error', 'danger', 'fail', 'failed' => 'danger',
            'ok', 'status'                     => 'success',
            default                            => strtolower($type),
        };
    }

    /**
     * Provide default icon classes if none supplied.
     */
    protected function defaultIcon(string $type): string
    {
        return match ($type) {
            'success' => 'fas fa-check faa-pulse animated',
            'danger'  => 'fas fa-exclamation-triangle faa-pulse animated',
            'warning' => 'fas fa-exclamation-triangle faa-pulse animated',
            default   => 'fas fa-info-circle faa-pulse animated',
        };
    }

    /**
     * Insert or replace an alert (if tag provided & already exists).
     */
    protected function pushAlert(array $data): void
    {
        if (empty($data['message'])) {
            return;
        }
    
        $alert = $this->buildAlert($data);
    
        if ($alert['tag'] !== null && $this->replaceTaggedAlert($alert)) {
            return;
        }
    
        $this->addAlert($alert);
    }

    protected function buildAlert(array $data): array
    {
        $type = $this->normalizeType($data['type'] ?? 'info');
    
        return [
            'id'          => uniqid('al_', true),
            'type'        => $type,
            'tag'         => $data['tag'] ?? null,
            'title'       => $data['title'] ?? null,
            'message'     => $data['message'],
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? $this->defaultIcon($type),
            'html'        => $data['html'] ?? false,
        'created_at'  => time(),
        ];
    }

    protected function replaceTaggedAlert(array $alert): bool
    {
        foreach ($this->liveAlerts as $index => $liveAlert) {
            if ($liveAlert['tag'] === $alert['tag']) {
                $this->liveAlerts[$index] = $alert;
                return true;
            }
        }
    
        return false;
    }

    protected function addAlert(array $alert): void
    {
        $this->liveAlerts[] = $alert;
    }

    /**
     * Dismiss everything (add a button if you want).
     */
    #[On('dismissAllNotifications')]
    public function dismissAll(): void
    {
        $this->liveAlerts = [];
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}