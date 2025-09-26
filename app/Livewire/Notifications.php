<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Notifications extends Component
{

    public array $liveAlerts = [];

    #[On('showNotification')]
    public function notify($type = null, $message = null, $tag = null): void
    {
        // Plain string fallback: treat as success
        if (is_string($type) && $message === null) {
            $this->pushAlert('success', $type, $tag);
            return;
        }

        if (!$type || !$message) {
            return;
        }

        $mapped = $this->mapType($type);
        $this->pushAlert($mapped, $message, $tag);
    }

    protected function mapType(string $type): string
    {
        return match (strtolower($type)) {
            'ok', 'status' => 'success',
            'danger', 'fail' => 'error',
            default => strtolower($type),
        };
    }
    
    protected function pushAlert(string $type, string $message, $tag): void
    {
        
        if($tag !== null) {
            foreach ($this->liveAlerts as $index => $liveAlert) {
                if ($liveAlert['tag'] === $tag) {
                    $this->liveAlerts[$index] = [
                        'id' => uniqid('al_', true),
                        'type' => $type === 'error' ? 'danger' : $type, // bootstrap 3 'danger'
                        'tag' => $tag,
                        'message' => $message,
                    ];
                    return;
                }
            } 
        }
        
        
        $this->liveAlerts[] = [
            'id' => uniqid('al_', true),
            'type' => $type === 'error' ? 'danger' : $type, // bootstrap 3 'danger'
            'tag' => $tag,
            'message' => $message,
        ];
    }
    
    #[On('dismissNotification')]
    public function dismiss(string $id): void
    {
        $this->liveAlerts = array_values(
            array_filter($this->liveAlerts, fn($a) => $a['id'] !== $id)
        );
    }

    #[On('dismissNotificationByTag')]
    public function dismissByTag(string $tag): void
    {
        $this->liveAlerts = array_values(
            array_filter($this->liveAlerts, fn($a) => $a['tag'] !== $tag)
        );
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
