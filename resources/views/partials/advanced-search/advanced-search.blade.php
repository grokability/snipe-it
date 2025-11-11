<div id="advancedSearchPanel" class="box box-default filter-sidebar">
    @push('css')
        <link rel="stylesheet" href="{{ mix('css/dist/advanced-search.min.css') }}">
    @endpush
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fas fa-filter"></i> <span class="filter-title"> {{ trans('general.advanced_search') }} </span>
        </h3>
        <div class="box-tools pull-right">
        <button type="button" id="closeSidebarButton" class="btn btn-box-tool collapse-toggle">
            <i class="fa fa-chevron-left icon-desktop" id="collapseIconDesktop"></i>
            <i class="fa fa-chevron-up icon-mobile" id="collapseIconMobile"></i>
        </button>
            <button id="topClearInputButton" type="button" class="btn btn-box-tool" aria-labelledby="topClearInputButtonLabel" >
                <i class="fa fa-times"></i> <span id="topClearInputButtonLabel" class="clear-text"> {{ trans('button.delete') }}</span>
            </button>
        </div>
    </div>
    
    <div class="box-body filter-body">
        <!-- Quick Filter Search -->
        <div class="form-group filter-content">
            <label>Search Filters</label>
            <input type="text" class="form-control" id="filterSearch" placeholder="{{ trans('general.search_after_filter_field') }}">
        </div>
        
        <!-- Predefined Filters -->
        <div class="form-group filter-content">
            @include ('partials.select.dropdowns.predefined-select', [
                'translated_name' => trans('general.select_predefined_filter'),
                'fieldname' => 'predefinedFilters',
                'select_id' => "predefinedfilters-select",
                'required' => 'false',
            ])
        </div>
        
        <hr class="filter-content">
        
        <!-- All Filter Fields -->
        <div id="advancedSearchFilters" class="filter-content container">
            @php
                $layoutJson = \App\Presenters\AssetPresenter::dataTableLayout();
                $layout = json_decode($layoutJson);
            @endphp

            @include('partials.advanced-search.search-inputs')
        </div>


    </div>
    @include ('partials.advanced-search.floating-button')

</div>

@include('partials.confetti-js', ['autostart' => false])

<script type="module">
import ApiService from '/js/dist/apiService.min.js';
import FilterFormManager from '/js/dist/filterFormManager.min.js';
import FloatingButtons from '/js/dist/floating-buttons.min.js';
import { container } from '/js/dist/simpleDIContainer.min.js';

// Add needed stuff into the DI container
container.register("apiService", new ApiService());
container.register("filterFormManager", new FilterFormManager());
container.register("floatingButtons", new FloatingButtons());

class FilterUIController {
    constructor(tableElement) {
        this.$table = tableElement;
        this.apiService = container.resolve("apiService");
        this.collector = container.resolve("filterFormManager");
        this.collector.collectFilterInputs();
    }

    refresh() {
        const filters = this.collector.collectFilterData();
        this.$table.bootstrapTable('refresh', {
            query: {
                filter: JSON.stringify(filters)
            }
        });
    }

    async updateFilterWithPredefined(event, selectedId = null) {
        if (event !== null) {
            selectedId = event?.target?.value;
        }

        const floatingButtons = container.resolve("floatingButtons");

        if (!selectedId) {
            floatingButtons.disableEditDeleteButtons();
            return;
        }

        floatingButtons.enableEditDeleteButtons();

        try {
            const response = await this.apiService.fetchPredefinedFilterData(selectedId);
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const data = await response.json();
            await this.collector.setValuesFromResponse(data.filter_data);
            this.refresh();

        } catch (err) {
            console.error("Failed to apply predefined filter:", err);
            Livewire.dispatch('showNotification', {
                type: 'error',
                message: '{{ trans("general.failed_to_apply_predefined_filter") }}'
            });
        }
    }

    storePredefinedFilterInBackend() {
        const filters = this.collector.collectFilterData();

        if (!filters || filters.length === 0) {
            Livewire.dispatch('showNotification', {
                type: "error",
                title: "{{ trans('general.error') }}",
                message: "{{ trans('general.can_not_save_empty_filter') }}"
            });
            return;
        }

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'create',
            predefinedFilterData: filters
        });
    }

    updatePredefinedFilterInBackend(updateFilterButtonId) {
        const updateBtn = document.getElementById(updateFilterButtonId);
        if (updateBtn.classList.contains('disabled')) return;

        const selectedFilter = $("#predefinedfilters-select").select2('data')[0];
        if (!selectedFilter) return;

        const filters = this.collector.collectFilterData();

        if (!filters || filters.length === 0) {
            Livewire.dispatch('showNotification', {
                type: "error",
                title: "{{ trans('general.error') }}",
                message: "{{ trans('general.can_not_update_empty_filter') }}"
            });
            return;
        }

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'edit',
            predefinedFilterId: parseInt(selectedFilter.id),
            predefinedFilterData: filters
        });
    }

    deletePredefinedFilterFromBackend(deleteFilterButtonId) {
        const deleteBtn = document.getElementById(deleteFilterButtonId);
        if (deleteBtn.classList.contains('disabled')) return;

        const selected = $("#predefinedfilters-select").select2('data')[0];
        if (!selected || !selected.id) return;

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'delete',
            predefinedFilterId: parseInt(selected.id)
        });
    }

    bindEvents() {
        $('#predefinedfilters-select').on('change', (e) => this.updateFilterWithPredefined(e));

        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.addEventListener('click', this.refresh.bind(this));
        }

        const clearButtons = ["clearInputButton", "topClearInputButton"];
        clearButtons.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.addEventListener('click', () => {
                    this.collector.clearAll();
                    $('#predefinedfilters-select').val(null).trigger('change');
                });
            }
        });

        const saveButton = document.getElementById("storeFilterButton");
        if (saveButton) {
            saveButton.addEventListener('click', () => this.storePredefinedFilterInBackend());
        }

        const updateButton = document.getElementById("updateFilterButton");
        if (updateButton) {
            updateButton.addEventListener('click', () => this.updatePredefinedFilterInBackend(updateButton.id));
        }

        const deleteButton = document.getElementById("deleteFilterButton");
        if (deleteButton) {
            deleteButton.addEventListener('click', () => this.deletePredefinedFilterFromBackend(deleteButton.id));
        }
    }
}

document.addEventListener('livewire:init', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    const controller = new FilterUIController($table);
    container.register("filterUiController", controller);
    controller.bindEvents();



    @if(isset($predefined_filter_id))
        controller.updateFilterWithPredefined(null, {{ $predefined_filter_id }});

        const filterSection = document.getElementById('filterSection');
        filterSection.classList.remove('hide');

        @if(!empty($predefined_filter_edit_modal_open) && $predefined_filter_edit_modal_open == true)
            sleep(200).then(() => {
                Livewire.dispatch('openPredefinedFiltersModal', {
                    action: 'edit',
                    predefinedFilterId: {{ (int) $predefined_filter_id }},
                    predefinedFilterData: {}
                });
            });
        @endif
    @else
        (async () => {
        setTimeout(async () => {
            const filterSection = document.getElementById('filterSection');
            filterSection.classList.remove('hide');
            container.resolve("filterFormManager").clearAll();

            await new Promise(resolve => setTimeout(resolve, 0));
            filterSection.classList.add('hide');
        }, 0);
    })();
    @endif
});

// Filter search functionality
document.getElementById('filterSearch').addEventListener('input', function (e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.filter-item');
    items.forEach(item => {
        const label = item.querySelector('label');
        const labelText = label ? label.textContent.toLowerCase() : '';
        item.style.display = labelText.includes(searchTerm) ? '' : 'none';
    });
});


</script>
