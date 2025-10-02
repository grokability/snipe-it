<?php

namespace App\Livewire\Partials\Advancedsearch;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

use App\Models\PredefinedFilter;
use App\Services\PredefinedFilterService;

enum FilterVisibility: string
{
    case Private = "private";
    case Public = "public";
}

enum AdvancedsearchModalAction: string
{
    case Create = "create";
    case Edit = "edit";
    case Delete = "delete";
}

class Modal extends Component
{
    public $showModal = false;
    public AdvancedsearchModalAction $modalActionType;

    #[Validate("required")]
    public ?string $name = "";
    public FilterVisibility $visibility = FilterVisibility::Private;
    public $groupSelect = [];
    public array $groupSelectOtherOptions = [];

    protected $listeners = ["groupSelect"];

    public ?int $filterId;
    public $filterData;

    #[On("openPredefinedFiltersModal")]
    public function openPredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService,
        string $action,
        ?array $predefinedFilterData = null,
        ?int $predefinedFilterId = null
    ) {
        $this->modalActionType = AdvancedsearchModalAction::from($action);
        $this->showModal = true;
        $this->groupSelect = [];
        $this->groupSelectOtherOptions = [];
        $this->filterData = $predefinedFilterData;
        $this->filterId = $predefinedFilterId;

        $user = auth()->user();
        $this->groupSelectOtherOptions = $user
            ->groups()
            ->pluck("id")
            ->toArray();

        if (
            $this->modalActionType === AdvancedsearchModalAction::Edit &&
            $predefinedFilterId !== null
        ) {
            $predefinedFilter = $predefinedFilterService->getFilterById(
                $predefinedFilterId
            );
            $this->name = $predefinedFilter["name"];

            if ($predefinedFilter["is_public"] === 1) {
                $this->visibility = FilterVisibility::Public;
            } else {
                $this->visibility = FilterVisibility::Private;
            }

            foreach ($predefinedFilter["permissions"] as $permission) {
                array_push(
                    $this->groupSelect,
                    $permission->permission_group_id
                );
            }

            $this->groupSelectOtherOptions = array_diff(
                $this->groupSelectOtherOptions,
                $this->groupSelect
            );
        }

        $this->dispatch("openPredefinedFiltersModalEvent");
    }

    #[On("closePredefinedFiltersModal")]
    public function closePredefinedFiltersModal()
    {
        $this->showModal = false;
        $this->dispatch("closePredefinedFiltersModalEvent");
    }

    #[On("savePredefinedFiltersModal")]
    public function savePredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService
    ) {
        $this->validate();

        $validated = [
            "name" => $this->name,
            "filter_data" => $this->filterData,
            "is_public" =>
                $this->visibility === FilterVisibility::Public ? 1 : 0,
            "permissions" => self::formatPermissions($this->groupSelect),
        ];

        $createFilterResponse = $predefinedFilterService->createFilter($validated);

        if ($createFilterResponse === true) {
            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => trans('general.notification_success'),
                'message' => trans('general.predefined_filter_saved_successfully'),
                'tag' => 'predefinedFilter',
            ]);
        } else {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('general.notification_error'),
                'tag' => 'predefinedFilter',
            ]);
        }

        $this->dispatch("savePredefinedFiltersModalEvent");
        $this->dispatch("closePredefinedFiltersModal");
    }

    #[On("updatePredefinedFiltersModal")]
    public function updatePredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService
    ) {
        $this->validate(); // You may want to adjust this if some fields are now optional

        $predefinedFilter = $predefinedFilterService->getFilterById($this->filterId);

        $validated = [
            'name' => $this->name ?? $predefinedFilter->name,
            'filter_data' => $this->filterData ?? $predefinedFilter->filter_data,
            'is_public' => isset($this->visibility)
                ? ($this->visibility === FilterVisibility::Public ? 1 : 0)
                : $predefinedFilter->is_public,
            'permissions' => !empty($this->groupSelect)
                ? self::formatPermissions($this->groupSelect)
                : $predefinedFilter->permissions,
        ];

        $updateFilterResponse = $predefinedFilterService->updateFilter($predefinedFilter, $validated);

        if ($updateFilterResponse === true) {
            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => trans('general.notification_success'),
                'message' => trans('general.predefined_filter_saved_successfully'),
                'tag' => 'predefinedFilter',
            ]);
        } else {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('general.notification_error'),
                'tag' => 'predefinedFilter',
            ]);
        }

        $this->dispatch("updatePredefinedFiltersModalEvent");
        $this->dispatch("closePredefinedFiltersModal");
    }

    #[On("deletePredefinedFiltersModal")]
    public function deletePredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService
    ) {


        $predefinedFilter = $predefinedFilterService->getFilterById($this->filterId);
        $deleteFilterResponse = $predefinedFilterService->deleteFilter($predefinedFilter);

        if ($deleteFilterResponse === true) {
            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => trans('general.notification_success'),
                'message' => trans('general.predefined_filter_saved_successfully'),
                'tag' => 'predefinedFilter',
            ]);
        } else {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('general.notification_error'),
                'tag' => 'predefinedFilter',
            ]);
        }

        $this->dispatch("deletePredefinedFiltersModalEvent");
        $this->dispatch("closePredefinedFiltersModal");
    }

    public function updateGroupSelect($values)
    {
        $this->groupSelect = is_array($values)
            ? $values
            : ($values
                ? [$values]
                : []);
    }

    public function render()
    {
        return view("livewire.partials.advancedsearch.modal");
    }

    private function getGroupSelectArrayAsArray(): array
    {
        if (is_array($this->groupSelect) === true) {
            return $this->groupSelect;
        }
        return [$this->groupSelect];
    }

    // FIXED: This was the main issue!
    private static function formatPermissions(array $permissions): array
    {
        $result = [];

        foreach ($permissions as $value) {
            $result[] = ["permission_group_id" => $value]; // FIXED: was "predefined_filter_id"
        }

        return $result;
    }
}
