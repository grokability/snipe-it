<?php

namespace App\Livewire\Partials\Advancedsearch;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

use App\Models\PredefinedFilter;
use App\Services\PredefinedFilterService;

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


    #[Validate('required')] 
    public ?string $name = '';
    public FilterVisibility $visibility = FilterVisibility::Private;
    public array $groupSelect = [];
    public array $groupSelectOtherOptions = [];

    public ?int $filterId;
    public string $filterData;

    #[On('openPredefinedFiltersModal')]
    public function openPredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService,
        string $action,
        string $predefinedFilterData,
        ?int $predefinedFilterId = null
    ) {
        $this->modalActionType = AdvancedsearchModalAction::from($action);
        $this->showModal = true;
        $this->groupSelect = []; // Empty groups array
        $this->groupSelectOtherOptions = [];
        $this->filterData = $predefinedFilterData;
        $this->filterId = $predefinedFilterId;

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
        $this->dispatch('closePredefinedFiltersModalEvent');
    }

    #[On('savePredefinedFiltersModal')]
    public function savePredefinedFiltersModal(PredefinedFilterService $predefinedFilterService)
    {
        // Convert visibility string back to enum if needed
        //$enumVisibility = FilterVisibility::from($this->visibility);

        $this->validate(); 
        
        if($this->modalActionType === AdvancedsearchModalAction::Create) {
            $predefinedFilter = new PredefinedFilter();
        } else {
            $predefinedFilter = $predefinedFilterService->getFilterById($this->filterId);
        }
        $predefinedFilter->name = $this->name;
        $predefinedFilter->filter_data = $this->filterData;
        
        if($this->visibility === FilterVisibility::Public) {
            $predefinedFilter->is_public = 1;
        } else {
            $predefinedFilter->is_public = 0;
        }
        
        dump($predefinedFilter->toArray());
        dump($predefinedFilter->getErrors());
        dump($predefinedFilter->isDirty());
        $predefinedFilter->save();

        $this->dispatch('savePredefinedFiltersModalEvent');
        $this->dispatch('closePredefinedFiltersModal');
    }

    public function render()
    {
        return view('livewire.partials.advancedsearch.modal');
    }
}
