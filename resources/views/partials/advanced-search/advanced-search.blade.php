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

<script>
class FilterUIController {
    constructor(tableElement) {
        this.$table = tableElement;
        this.collector = new FilterFormManager();
    }

    refresh() {
        const filters = this.collector.collect();
        this.$table.bootstrapTable('refresh', {
            query: {
                filter: JSON.stringify(filters)
            }
        });
    }

updateFilterWithPredefined(event, selectedId = null) {
    if(event !== null) {
        selectedId = event?.target?.value;
    }

    if (!selectedId) {
        floatingMenuDisableEditDeleteButtons();
        return;
    }

    floatingMenuEnableEditDeleteButtons();
    setAdvancedSearchPanelFilterEnabledState(true);

    this.fetchPredefinedFilterData(selectedId)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json(); // Parse JSON from response
        })
        .then(data => {
            if (!data.filter_data) return;

            return this.collector.setValuesFromResponse(data.filter_data)
                .then(() => this.refresh());
        })
        .catch(err => {
            console.error("Failed to apply predefined filter:", err);
            Livewire.dispatch('showNotification', { type: 'error', message: '{{ trans('general.failed_to_apply_predefined_filter') }}'});
            setAdvancedSearchPanelFilterEnabledState(false);
        });
}



    storePredefinedFilterInBackend() {
        const filters = this.collector.collect();
        
        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'create',
            predefinedFilterData: filters,
        });

    }

    updatePredefinedFilterInBackend(updateFilterButtonId) {
        if (document.getElementById(updateFilterButtonId).classList.contains('disabled')) return; // Do nothing when the button is disabled

        const selectedFilter = $("#predefinedfilters-select").select2('data')[0]; // Always zero because only one element can be selected at the time
        if (!selectedFilter) return;
        const filters = this.collector.collect();

        Livewire.dispatch('openPredefinedFiltersModal', {
            action: 'edit',
            predefinedFilterId: parseInt(selectedFilter.id),
            predefinedFilterData: filters,
        });

    }

    deletePredefinedFilterFromBackend(deleteFilterButtonId) {
        if (document.getElementById(deleteFilterButtonId).classList.contains('disabled')) return; // Do nothing when the button is disabled

        const selectedFilterId = $("#predefinedfilters-select").select2('data')[0].id; // Always zero because only one element can be selected at the time
        if (!selectedFilterId) return;

        Livewire.dispatch('openPredefinedFiltersModal',{ 
            action: 'delete',
            predefinedFilterId: parseInt(selectedFilterId),
        });
    }

    fetchPredefinedFilterData(filterId) {
        const updateUrlTemplate = `{{ route('api.predefined-filters.show', ['id' => '__ID__']) }}`;
        const finalUrl = updateUrlTemplate.replace('__ID__', filterId);

        return fetchFromBackend('GET', finalUrl);
    }

    bindEvents() {

        $('#predefinedfilters-select').on('change', (e) => {
            this.updateFilterWithPredefined(e);
        });

        const filterButton = document.getElementById("filterButton");
        if (filterButton) {
            filterButton.addEventListener('click', this.refresh.bind(this));
        }

        const clearButton = document.getElementById("clearInputButton");
        if (clearButton) {
            clearButton.addEventListener('click', () => this.collector.clearAll());
        }

        const topClearButton = document.getElementById("topClearInputButton");
        if (topClearButton) {
            topClearButton.addEventListener('click', () => this.collector.clearAll());
            //Livewire.emit('refreshNotifications');
        }

        const saveFilterButton = document.getElementById("storeFilterButton");
        if (saveFilterButton) {
            saveFilterButton.addEventListener('click', () => this.storePredefinedFilterInBackend());
        }

        const updateFilterButton = document.getElementById("updateFilterButton");
        if (updateFilterButton) {
            updateFilterButton.addEventListener('click', () => this.updatePredefinedFilterInBackend(updateFilterButton.id));
        }

        const deleteFilterButton = document.getElementById("deleteFilterButton");
        if (deleteFilterButton) {
            deleteFilterButton.addEventListener('click', () => this.deletePredefinedFilterFromBackend(deleteFilterButton.id));
        }

    }
}

document.addEventListener('livewire:init', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);
    
    // Initialize everything
    const controller = new FilterUIController($table);
    controller.bindEvents();

    function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}


    @if(isset($predefined_filter_id))
        controller.updateFilterWithPredefined(null, {{ $predefined_filter_id }});

        const filterSection = document.getElementById('filterSection');
        filterSection.classList.remove('hide');

        @if(!empty($predefined_filter_edit_modal_open) && $predefined_filter_edit_modal_open == true)
            sleep(200).then(() => { // I know that this is bad practice but I havn't found another way that works :-(
                Livewire.dispatch('openPredefinedFiltersModal', {
                    action: 'edit',
                    predefinedFilterId: {{ (int) $predefined_filter_id }},
                    predefinedFilterData: {},
                });
            });
        @endif
    @endif

});

// Filter search functionality
document.getElementById('filterSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.filter-item');
    items.forEach(item => {
        const label = item.querySelector('label');
        const labelText = label ? label.textContent.toLowerCase() : '';
        if (labelText.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function fetchFromBackend(method, path, body = null) {
    const options = {
        method: method,
        headers: {
            accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Content-Type': 'application/json'
        },
        ...(body && { body })
    };

    return fetch(path, options);
        //.then(res => res.json());
}

function fetchItemFromBackendById(type, id) {
    const typeMap = {
        asset: "hardware",
        category: "categories",
        company: "companies",
        location: "locations",
        manufacturer: "manufacturers",
        model: "models",
        groups: "groups",
        group_select: "predefinedFilters",
        rtd_location: "locations",
        status_label: "statuslabels",
        supplier: "suppliers",
        user: "users"
    };

    if (!typeMap[type]) {
        return Promise.reject(`Invalid type ${type}`);
    }
    const path = `/api/v1/${typeMap[type]}/${id}`;
    return fetchFromBackend('GET', path);
}

function predefinedFilterRequest(method, filterId = null, filterData = null) {
    let  path = "/api/v1/predefinedFilters";

    if(filterId !== null) {
        path += "/" + filterId;
    }

    return fetchFromBackend(method, path, filterData);
}

function setAdvancedSearchPanelFilterEnabledState(state) {
    const fields = document.getElementById("advancedSearchPanel").getElementsByTagName('*');
    for(let i = 0; i < fields.length; i++)
    {
        fields[i].disabled = state;
    }
}

</script>
