<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Notifications extends Component
{

    public array $liveAlerts = [];

    #[On('notify')]
    public function notify($type = null, $message = null): void
    {
        // Plain string fallback: treat as success
        if (is_string($type) && $message === null) {
            $this->pushAlert('success', $type);
            return;
        }

        if (!$type || !$message) {
            return;
        }

        $mapped = $this->mapType($type);
        $this->pushAlert($mapped, $message);
    }

    protected function mapType(string $type): string
    {
        return match (strtolower($type)) {
            'ok', 'status' => 'success',
            'danger', 'fail' => 'error',
            default => strtolower($type),
        };
    }

    protected function pushAlert(string $type, string $message): void
    {
        $this->liveAlerts[] = [
            'id' => uniqid('al_', true),
            'type' => $type === 'error' ? 'danger' : $type, // bootstrap 3 'danger'
            'message' => $message,
        ];
    }

    public function dismiss(string $id): void
    {
        $this->liveAlerts = array_values(
            array_filter($this->liveAlerts, fn($a) => $a['id'] !== $id)
        );
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
