<?php

namespace App\Livewire\Partials\Advancedsearch;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

use App\Models\PredefinedFilter;
use App\Services\PredefinedFilterService;
use App\Models\PermissionGroup;

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
    public FilterVisibility $visibility = FilterVisibility::Private;

    #[Validate("required")]
    public ?string $name = "";

    #[Validate("sometimes")]
    public $groupSelect = [];

    #[Validate("sometimes")]
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

        // If the user a superuser show him all groups
        if ($user->isSuperUser()) {
            $this->groupSelectOtherOptions = PermissionGroup::all()->pluck("id")->toArray();

        } else {
            // Show only the groups there the user is member of
            $this->groupSelectOtherOptions = $user
                ->groups()
                ->pluck("id")
                ->toArray();
        }

        if (
            $this->modalActionType === AdvancedsearchModalAction::Edit &&
            $predefinedFilterId !== null
        ) {
            $predefinedFilter = $predefinedFilterService->getFilterWithOptionalPermissionsById(
                $predefinedFilterId
            );
            $this->name = $predefinedFilter["name"];

            if ($predefinedFilter["is_public"] == 1) {
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
        $this->groupSelect = [];
        $this->groupSelectOtherOptions = [];
        $this->filterData = null;
        $this->filterId = null;
        $this->name = "";
        $this->dispatch("closePredefinedFiltersModalEvent");
    }

    #[On("savePredefinedFiltersModal")]
    public function savePredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService
    ) {
        $this->validate();

        if(mb_strlen($this->name) > 190) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.name_too_long'),
                'tag' => 'predefinedFilter',
            ]);
            return;
        }

        $filter = new PredefinedFilter();

        // Enforce: only allow creation if private or groups selected
        if ($this->visibility === FilterVisibility::Public) {

            if (!$this->checkCreatePermissions()) {
                $this->dispatch('showNotificationInFrontend', [
                    'type' => 'error',
                    'title' => trans('general.notification_error'),
                    'message' => trans('admin/predefinedFilters/message.create.not_allowed'),
                    'tag' => 'predefinedFilter',
                ]);

                $this->dispatch("closePredefinedFiltersModal");
                return;
            }

            if (empty($this->groupSelect) || count($this->groupSelect) === 0) {
                $this->dispatch('showNotificationInFrontend', [
                    'type' => 'error',
                    'title' => trans('general.notification_error'),
                    'message' => trans('admin/predefinedFilters/message.update.at_least_one_is_group_required_for_public_filter'),
                    'tag' => 'predefinedFilter',
                ]);
                return;
            }
        }

        if ($filter->checkIfNameAlreadyExists($this->name)) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'warning',
                'title' => trans('general.notification_warning'),
                'message' => trans('admin/predefinedFilters/message.filter_duplicate_name'),
                'tag' => 'predefinedFilter',
            ]);
        }

        $validated = [
            "name" => $this->name,
            "filter_data" => $this->filterData,
            "is_public" =>
                $this->visibility === FilterVisibility::Public ? 1 : 0,
            "permissions" => self::formatPermissions($this->groupSelect),
        ];

        $predefinedFilterService->createFilter($validated);

        $this->dispatch('showNotificationInFrontend', [
            'type' => 'success',
            'title' => trans('general.notification_success'),
            'message' => trans('admin/predefinedFilters/message.create.success'),
            'tag' => 'predefinedFilter',
        ]);

        $this->dispatch("savePredefinedFiltersModalEvent");
        $this->dispatch("closePredefinedFiltersModal");
    }

    #[On("updatePredefinedFiltersModal")]
    public function updatePredefinedFiltersModal(
        PredefinedFilterService $predefinedFilterService
    ) {
        $this->validate([
            'name' => 'required|string',
            'filterData' => 'array',
            'groupSelect' => 'array',
            'groupSelect.*' => 'required|integer|exists:permission_groups,id',
        ]);

        if(mb_strlen($this->name) > 190) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.name_too_long'),
                'tag' => 'predefinedFilter',
            ]);
            return;
        }

        // Enforce: only allow update if private or groups selected
        if ($this->visibility === FilterVisibility::Public && (empty($this->groupSelect) || count($this->groupSelect) === 0)) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.update.at_least_one_is_group_required_for_public_filter'),
                'tag' => 'predefinedFilter',
            ]);
            return;
        }

        $predefinedFilter = PredefinedFilter::find($this->filterId);

        if (!isset($predefinedFilter)) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.does_not_exist'),
                'tag' => 'predefinedFilter',
            ]);
            return;
        }
        
        if ($this->visibility === FilterVisibility::Public && !$predefinedFilter->is_public && !$this->checkCreatePermissions() ) {
            
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.create.not_allowed'),
                'tag' => 'predefinedFilter',
            ]);
                    
            $this->dispatch("closePredefinedFiltersModal");
            return;
        }
        
        if (!$predefinedFilter->userHasPermission(auth()->user(), 'edit')) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.update.not_allowed_to_edit'),
                'tag' => 'predefinedFilter',
            ]);
            $this->dispatch("updatePredefinedFiltersModalEvent");
            $this->dispatch("closePredefinedFiltersModal");
            return;
        }

        $validated = [
            'name' => $this->name ?? $predefinedFilter->name,
            'filter_data' => $this->filterData ?? $predefinedFilter->filter_data,
            'is_public' => isset($this->visibility)
                ? ($this->visibility === FilterVisibility::Public ? 1 : 0)
                : $predefinedFilter->is_public,
            'permissions' => self::formatPermissions($this->getGroupSelectArrayAsArray()),
        ];

        if ($predefinedFilter->checkIfNameAlreadyExists($this->name, $predefinedFilter->id)) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'warning',
                'title' => trans('general.notification_warning'),
                'message' => trans('admin/predefinedFilters/message.filter_duplicate_name'),
                'tag' => 'predefinedFilter',
            ]);
        }
        
        $updateFilterResponse = $predefinedFilterService->updateFilter($predefinedFilter, $validated);

        if ($updateFilterResponse["validationErrors"] === null) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'success',
                'title' => trans('general.notification_success'),
                'message' => trans('admin/predefinedFilters/message.update.success'),
                'tag' => 'predefinedFilter',
            ]);
        } else {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.update.validation_error'),
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


        $predefinedFilter = $predefinedFilterService->getFilterWithOptionalPermissionsById($this->filterId);

        if (!isset($predefinedFilter)) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.does_not_exist'),
                'tag' => 'predefinedFilter',
            ]);
        }

        if (!$predefinedFilter->userHasPermission(auth()->user(), 'delete')) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.delete.not_allowed_to_delete'),
                'tag' => 'predefinedFilter',
            ]);
            $this->dispatch("deletePredefinedFiltersModalEvent");
            $this->dispatch("closePredefinedFiltersModal");
            return;
        }

        $deleteFilterResponse = $predefinedFilterService->deleteFilter($predefinedFilter);

        if ($deleteFilterResponse === true) {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'success',
                'title' => trans('general.notification_success'),
                'message' => trans('admin/predefinedFilters/message.delete.success'),
                'tag' => 'predefinedFilter',
            ]);
        } else {
            $this->dispatch('showNotificationInFrontend', [
                'type' => 'error',
                'title' => trans('general.notification_error'),
                'message' => trans('admin/predefinedFilters/message.delete.error'),
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

    private static function formatPermissions(array $permissions): array
    {
        $result = [];

        foreach ($permissions as $value) {
            $result[] = ["permission_group_id" => $value];
        }

        return $result;
    }

    private function checkCreatePermissions(): bool{
        $filter = new PredefinedFilter();
                
        // create dummy filter
        $filter->is_public = true;
        $filter->filter_data = [];
        $filter->created_by = auth()->user()->id;

        if ($filter->userHasPermission(auth()->user(), 'create')) {
            return true;
        }

        return false;
    }
}
