<?php

namespace App\Livewire\Partials\Advancedsearch;

use Livewire\Component;
use Livewire\Attributes\On;

use App\Services\PredefinedFilterService;
use App\Models\User;

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
    public array $groupSelect = [];
    public array $groupSelectOtherOptions = [];

    #[On('openPredefinedFiltersModal')]
    public function openPredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService,
        string $action,
        ?int $predefinedFilterId = null
    ) {
        $this->modalActionType = AdvancedsearchModalAction::from($action);
        $this->showModal = true;
        $this->groupSelect = []; // Empty groups array
        $this->groupSelectOtherOptions = [];

        $user = auth()->user();
        $this->groupSelectOtherOptions = $user->groups()->pluck('id')->toArray();

        if($this->modalActionType === AdvancedsearchModalAction::Edit && $predefinedFilterId !== null) {
            $predefinedFilter = $predefinedFilterService->getFilterById($predefinedFilterId);
            $this->name = $predefinedFilter['name'];

            if($predefinedFilter['is_public'] === 1) {
                $this->visibility = FilterVisibility::Public;
            } else {
                $this->visibility = FilterVisibility::Private;
            }

            foreach($predefinedFilter['permissions'] as $permission) {
                array_push($this->groupSelect, $permission->permission_group_id);
            }

            $this->groupSelectOtherOptions = array_diff($this->groupSelectOtherOptions, $this->groupSelect);
        }


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
