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
    public $groupSelect = [];
    public array $groupSelectOtherOptions = [];

    protected $listeners = [
        'groupSelect',
    ];

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

        if ($this->modalActionType === AdvancedsearchModalAction::Edit && $predefinedFilterId !== null) {
            $predefinedFilter = $predefinedFilterService->getFilterById($predefinedFilterId);
            $this->name = $predefinedFilter['name'];

            if ($predefinedFilter['is_public'] === 1) {
                $this->visibility = FilterVisibility::Public;
            } else {
                $this->visibility = FilterVisibility::Private;
            }

            foreach ($predefinedFilter['permissions'] as $permission) {
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
        $this->validate();

        $validated = [
            'name' => $this->name,
            'filter_data' => $this->filterData,
            'is_public' => $this->visibility === FilterVisibility::Public ? 1 : 0,
            // Add permissions if needed:
            'permissions' => $this->groupSelect,
        ];

        dump($this->groupSelect);
        $r = $predefinedFilterService->createFilter($validated);
        dump($r->save());

        $this->dispatch('savePredefinedFiltersModalEvent');
        $this->dispatch('closePredefinedFiltersModal');
    }

    public function updateGroupSelect($values)
    {
        $this->groupSelect = is_array($values) ? $values : ($values ? [$values] : []);
    }


    public function render()
    {
        return view('livewire.partials.advancedsearch.modal');
    }

    private function getGroupSelectArrayAsArray(): array
    {
        if (is_array($this->groupSelect) === true) {
            return $this->groupSelect;
        }
        return [$this->groupSelect];
    }
    static private function formatPermissions(array $permissions): array
    {
        $result = [];

        foreach ($permissions as $value) {
            $result[] = ["predefined_filter_id" => $value];
        }

        return $result;
    }
}
