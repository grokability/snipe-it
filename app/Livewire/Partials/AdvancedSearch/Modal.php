<?php

namespace App\Livewire\Partials\Advancedsearch;

use Livewire\Component;
use Livewire\Attributes\On;

enum FilterVisibility: string
{
    case Private = 'private';
    case Public = 'public';
}

enum AdvancedsearchModalAction: string
{
    case Create = 'create';
    case Edit = 'edit';
}

class Modal extends Component
{
    public $showModal = false;
    public AdvancedsearchModalAction $modalActionType;


    public ?string $name = '';
    public FilterVisibility $visibility = FilterVisibility::Private;
    public array $groups = [];

    #[On('openPredefinedFiltersModal')]
    public function openPredefinedFiltersModal(string $action, ?int $predefinedFilterId = null)
    {
        $this->modalActionType = AdvancedsearchModalAction::from($action);
        //dump($modalActionType);
        //dump($predefinedFilterId);

        $this->showModal = true;

        // use $predefinedFilterId, $name as needed

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
        //$enumVisibility = FilterVisibility::from($this->visibility);

        // Handle saving logic here...

        $this->dispatch('closePredefinedFiltersModalEvent'); // JS listener closes the modal
    }

    public function render()
    {
        return view('livewire.partials.advancedsearch.modal');
    }
}
