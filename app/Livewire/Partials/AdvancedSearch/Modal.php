<?php

namespace App\Livewire\Partials\Advancedsearch;

use Livewire\Component;
use Livewire\Attributes\On;

enum FilterVisibility: string
{
    case Private = 'private';
    case Public = 'public';
}

class Modal extends Component
{
    public $showModal = false;

    public string $name = '';
    public string $visibility = 'private'; // Use string binding for Livewire compatibility
    public array $groups = [];

    #[On('openPredefinedFiltersModal')]
    public function openPredefinedFiltersModal($name = '', $visibility = 'private', $groups = [])
    {
        $this->showModal = true;

        $this->name = $name;
        $this->visibility = $visibility === 'public'
            ? FilterVisibility::Public->value
            : FilterVisibility::Private->value;

        $this->groups = is_array($groups) ? $groups : [];

        $this->dispatch('openPredefinedFiltersModalEvent');
    }

    #[On('closePredefinedFiltersModal')]
    public function closePredefinedFiltersModal()
    {
        $this->showModal = false;

        // TODO: Get inputs from modal

        $this->dispatch('closePredefinedFiltersModalEvent');
    }

    public function saveFilter()
    {
        // Convert visibility string back to enum if needed
        $enumVisibility = FilterVisibility::from($this->visibility);

        // Handle saving logic here...

        $this->dispatch('close-modal'); // JS listener closes the modal
    }

    public function render()
    {
        return view('livewire.partials.advancedsearch.modal');
    }
}
