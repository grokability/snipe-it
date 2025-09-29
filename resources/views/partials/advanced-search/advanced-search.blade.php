<div id="advancedSearchPanel" class="box box-default filter-sidebar">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fas fa-filter"></i> <span class="filter-title"> {{ trans('general.advanced_search') }} </span>
        </h3>
        <div class="box-tools pull-right">
            <button type="button" id="closeSidebarButton" class="btn btn-box-tool collapse-toggle">
                <i class="fa fa-chevron-left icon-desktop" id="collapseIconDesktop"></i>
                <i class="fa fa-chevron-up icon-mobile" id="collapseIconMobile"></i>
            </button>
            <button id="topClearInputButton" type="button" class="btn btn-box-tool">
                <i class="fa fa-times"></i> <span class="clear-text"> {{ trans('button.delete') }}</span>
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
    @include ('partials.advanced-search.modal', ['createNew' => false])
</div>

@include('partials.confetti-js', ['autostart' => false])

<script>
let controller = {};

class FilterUIController {
    constructor(tableElement) {
        this.$table = tableElement;
        this.collector = new FilterFormManager();
    }

    refresh() {
        const filters = this.collector.collect();
        this.$table.bootstrapTable('refresh', {
            query: { filter: JSON.stringify(filters) }
        });
    }

    /**
     * Apply a predefined filter by its id.
     * Returns a promise so callers can chain actions (like opening the edit modal).
     */
    updateFilterWithPredefined(event, selectedId = null) {
        if (selectedId === null) {
            selectedId = event?.target?.value;
        }

        if (!selectedId) {
            floatingMenuDisableEditDeleteButtons();
            return Promise.resolve();
        }

        floatingMenuEnableEditDeleteButtons();
        setAdvancedSearchPanelFilterEnabledState(true);

        return this.fetchPredefinedFilterData(selectedId)
            .then(response => {
                if (!response.ok) throw new Error("Network response was not ok");
                return response.json();
            })
            .then(data => {
                if (!data.filter_data) return;
                return this.collector.setValuesFromResponse(data.filter_data)
                    .then(() => this.refresh());
            })
            .catch(err => {
                console.error("Failed to apply predefined filter:", err);
                alert("Failed to apply predefined filter");
                setAdvancedSearchPanelFilterEnabledState(false);
            });
    }

    storePredefinedFilterInBackend() {
        const filters = this.collector.collect();
        openFilterCreateUpdateModal(true)
            .then(input => {
                const payload = {
                    name: input.name,
                    filter_data: filters,
                    permissions: input.permissions,
                    is_public: input.visibility === "public"
                };
                fetchFromBackend('POST', '{{ route('api.predefined-filters.store') }}', JSON.stringify(payload))
                    .then(response => {
                        if (response.status === 201) {
                            alert("Filter stored successfully");
                            if (window.triggerConfetti) window.triggerConfetti();
                        } else {
                            console.error(response);
                            alert("An error has occured. Look in the browser console for more details.");  
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        alert("An error has occured: " + error);
                    });
            });
    }

    updatePredefinedFilterInBackend(updateFilterButtonId = null) {
        if (updateFilterButtonId !== null) {
            if (document.getElementById(updateFilterButtonId).classList.contains('disabled')) return;
        }

        const selectedFilter = $("#predefinedfilters-select").select2('data')[0];
        if (!selectedFilter) return;
        const filters = this.collector.collect();

        fetchItemFromBackendById("group_select", selectedFilter.id)
            .then(response => {
                response.json()
                    .then(responseJson => {
                        const permissionGroupRequests = responseJson.permissions.map(
                            permission => fetchItemFromBackendById("groups", permission.permission_group_id)
                        );
                        Promise.all(permissionGroupRequests)
                            .then(permissionGroupResponses => Promise.all(permissionGroupResponses.map(r => r.json())))
                            .then(permissionGroupResponses => {
                                openFilterCreateUpdateModal(false, responseJson.name, permissionGroupResponses)
                                    .then(input => {
                                        const payload = {
                                            name: input.name,
                                            filter_data: filters,
                                            permissions: input.permissions,
                                            is_public: input.visibility === "public"
                                        };
                                        const updateUrlTemplate = `{{ route('api.predefined-filters.update', ['id' => '__ID__']) }}`;
                                        const finalUrl = updateUrlTemplate.replace('__ID__', selectedFilter.id);

                                        fetchFromBackend('PUT', finalUrl, JSON.stringify(payload))
                                            .then(response => {
                                                if (response.status === 200) {
                                                    alert("Filter updated successfully");
                                                    if (window.triggerConfetti) window.triggerConfetti();
                                                } else {
                                                    console.error(response);
                                                    alert("An error has occured. Look in the browser console for more details.");
                                                }
                                            });
                                    });
                            });
                    })
                    .catch(error => {
                        console.error(error);
                        alert("An error has occured: " + error);
                    });
            });
    }

    deletePredefinedFilterFromBackend(deleteFilterButtonId) {
        if (document.getElementById(deleteFilterButtonId).classList.contains('disabled')) return;

        const selectedFilterId = $("#predefinedfilters-select").select2('data')[0]?.id;
        if (!selectedFilterId) return;

        const updateUrlTemplate = `{{ route('api.predefined-filters.destroy', ['id' => '__ID__']) }}`;
        const finalUrl = updateUrlTemplate.replace('__ID__', selectedFilterId);

        fetchFromBackend('PUT', finalUrl)
            .then(response => {
                if (response.status === 200) {
                    alert("Filter deleted successfully");
                    if (window.triggerConfetti) window.triggerConfetti();
                } else {
                    console.error(response);
                    alert("An error has occured. Look in the browser console for more details.");  
                }
            })
            .catch(error => {
                console.error(error);
                alert("An error has occured: " + error);
            });
    }

    fetchPredefinedFilterData(filterId) {
        const updateUrlTemplate = `{{ route('api.predefined-filters.show', ['id' => '__ID__']) }}`;
        const finalUrl = updateUrlTemplate.replace('__ID__', filterId);
        return fetchFromBackend('GET', finalUrl);
    }

    bindEvents() {
        $('#predefinedfilters-select').on('change', (e) => {
            // Only handle user-triggered changes; programmatic apply is handled elsewhere
            this.updateFilterWithPredefined(e);
        });

        const filterButton = document.getElementById("filterButton");
        if (filterButton) filterButton.addEventListener('click', this.refresh.bind(this));

        const clearButton = document.getElementById("clearInputButton");
        if (clearButton) clearButton.addEventListener('click', () => this.collector.clearAll());

        const topClearButton = document.getElementById("topClearInputButton");
        if (topClearButton) topClearButton.addEventListener('click', () => this.collector.clearAll());

        const saveFilterButton = document.getElementById("storeFilterButton");
        if (saveFilterButton) saveFilterButton.addEventListener('click', () => this.storePredefinedFilterInBackend());

        const updateFilterButton = document.getElementById("updateFilterButton");
        if (updateFilterButton) updateFilterButton.addEventListener('click', () => this.updatePredefinedFilterInBackend(updateFilterButton.id));

        const deleteFilterButton = document.getElementById("deleteFilterButton");
        if (deleteFilterButton) deleteFilterButton.addEventListener('click', () => this.deletePredefinedFilterFromBackend(deleteFilterButton.id));
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    controller = new FilterUIController($table);
    controller.bindEvents();

    // Auto-apply predefined filter if provided
    const predefinedFilterId = @json($predefined_filter_id);
    const predefinedFilterEditModalOpen = @json($predefined_filter_edit_modal_open);
    const filterSection = document.getElementById('advancedSearchPanel');

    if (predefinedFilterId) {
        // Make sure the panel is visible
        if (filterSection && filterSection.classList.contains('hide')) {
            filterSection.classList.remove('hide');
        }

        // Set the select's value WITHOUT triggering the change event (we manually apply)
        $('#predefinedfilters-select').val(predefinedFilterId);

        // Apply the filter and optionally open the modal after it's loaded
        controller.updateFilterWithPredefined(null, predefinedFilterId)
            ?.then(() => {
                if (predefinedFilterEditModalOpen) {
                    controller.updatePredefinedFilterInBackend();
                }
            });
    }
});

// Filter search functionality
document.getElementById('filterSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    document.querySelectorAll('.filter-item').forEach(item => {
        const label = item.querySelector('label');
        const labelText = label ? label.textContent.toLowerCase() : '';
        item.style.display = labelText.includes(searchTerm) ? '' : 'none';
    });
});

function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function fetchFromBackend(method, path, body = null) {
    const options = {
        method,
        headers: {
            accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Content-Type': 'application/json'
        },
        ...(body && { body })
    };
    return fetch(path, options);
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
    if (!typeMap[type]) return Promise.reject(`Invalid type ${type}`);
    return fetchFromBackend('GET', `/api/v1/${typeMap[type]}/${id}`);
}

function predefinedFilterRequest(method, filterId = null, filterData = null) {
    let path = "/api/v1/predefinedFilters";
    if (filterId !== null) path += "/" + filterId;
    return fetchFromBackend(method, path, filterData);
}

function setAdvancedSearchPanelFilterEnabledState(state) {
    const fields = document.getElementById("advancedSearchPanel").getElementsByTagName('*');
    for (let i = 0; i < fields.length; i++) {
        fields[i].disabled = state;
    }
}
</script>

<style>
.filter-sidebar {
    transition: all 0.3s ease;
    position: relative;
}
.filter-body {
    transition: all 0.3s ease;
    overflow: hidden;
}
.filter-content {
    transition: opacity 0.2s ease;
}
.filter-title,
.clear-text {
    transition: opacity 0.2s ease;
}
@media (max-width: 768px) {
    .filter-sidebar {
        width: 100% !important;
        margin-bottom: 15px;
    }
}
.container {
    width: 100%;
    margin: 0 auto;
    padding: 10px;
    box-sizing: border-box;
}
#advancedSearchFilters {
    display: block;
    max-width: 100%;
    margin: 0;
}
.box-body {
    overflow-y: auto;
    padding: 15px;
}
@media (max-width: 768px) {
  .box-body { max-height: 75vh; }
}
@media (min-width: 769px) {
  .box-body { height: 100%; }
}
.box-body::-webkit-scrollbar { width: 6px; }
.collapse-toggle { margin-right: 5px; }
.icon-desktop,
.icon-mobile { display: none; }
@media (min-width: 768px) {
  .icon-desktop { display: inline; }
}
@media (max-width: 767px) {
  .icon-mobile { display: inline; }
}
</style>