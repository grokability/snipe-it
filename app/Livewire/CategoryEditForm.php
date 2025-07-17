<?php

namespace App\Livewire;

use Livewire\Component;

class CategoryEditForm extends Component
{
    public bool $alertOnResponse;

    public $defaultEulaText;

    public $eulaText;

    public $originalSendCheckInEmailValue;

    public bool $requireAcceptance;

    public bool $sendCheckInEmail;

    public bool $useDefaultEula;

    public function mount()
    {
        $this->originalSendCheckInEmailValue = $this->sendCheckInEmail;

        if ($this->eulaText || $this->useDefaultEula) {
            $this->sendCheckInEmail = 1;
        }
    }

    public function render()
    {
        return view('livewire.category-edit-form');
    }

    public function updated($property, $value)
    {
        if (! in_array($property, ['eulaText', 'useDefaultEula'])) {
            return;
        }

        $this->sendCheckInEmail = $this->eulaText || $this->useDefaultEula ? 1 : $this->originalSendCheckInEmailValue;
    }

    public function getShouldDisplayEmailMessageProperty(): bool
    {
        return $this->eulaText || $this->useDefaultEula;
    }

    public function getEmailMessageProperty(): string
    {
        if ($this->useDefaultEula) {
            return trans('admin/categories/general.email_will_be_sent_due_to_global_eula');
        }

        return trans('admin/categories/general.email_will_be_sent_due_to_category_eula');
    }

    public function getEulaTextDisabledProperty()
    {
        return (bool)$this->useDefaultEula;
    }

    public function getSendCheckInEmailDisabledProperty()
    {
        return $this->eulaText || $this->useDefaultEula;
    }
}
