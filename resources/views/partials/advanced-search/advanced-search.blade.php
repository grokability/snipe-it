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

    async updateFilterWithPredefined(event) {
        const selectedId = event?.target?.value;
        if (!selectedId) return;

        setAdvancedSearchPanelFilterEnabledState(true);

        try {
            const filterData = await this.fetchPredefinedFilterData(selectedId);
            if (!filterData) return;

            await this.collector.setValuesFromResponse(filterData.filter_data); // Await here
            this.refresh();
        } catch (err) {
            console.error("Failed to apply predefined filter:", err);
            alert("Failed to apply predefined filter");
            setAdvancedSearchPanelFilterEnabledState(false);
        }
    }


    storePredefinedFilterInBackend() {
        const filters = this.collector.collect();
        const name = prompt("Enter the name of the filter");
        const payload = {
            name: name,
            filter_data: filters,
        };
        fetchFromBackend('POST', `/api/v1/predefinedFilters`, JSON.stringify(payload))
        .then((response) => {
            if(response.message === "Template saved successfully") {
                alert("Filter stored successfully");
                if(window.triggerConfetti) window.triggerConfetti();
            } else {
                console.error(response);
                alert("An error has occured. Look in the browser console for more details.");  
            }
        })
        .catch((error) => {
            console.error(error);
            alert("An error has occured: " + error);
        })
    }

    updatePredefinedFilterInBackend() {
        const selectedFilter = $("#predefinedfilters-select").select2('data')[0]; // Always zero because only one element can be selected at the time
        if (!selectedFilter) return;
        const filters = this.collector.collect();
        const name = prompt("Enter the name of the filter", selectedFilter.text);

        const payload = {
            name: name,
            filter_data: filters,
        };

        fetchFromBackend('PUT', `/api/v1/predefinedFilters/${selectedFilter.id}`, JSON.stringify(payload))
        .then((response) => {
            if(response.message === "Template updated successfully") {
                alert("Filter stored successfully");
                if(window.triggerConfetti) window.triggerConfetti();
            } else {
                console.error(response);
                alert("An error has occured. Look in the browser console for more details.");  
            }
        })
        .catch((error) => {
            console.error(error);
            alert("An error has occured: " + error);
        })
    }

    deletePredefinedFilterFromBackend() {
        const selectedId = $("#predefinedfilters-select").select2('data')[0].id; // Always zero because only one element can be selected at the time
        if (!selectedId) return;

        fetchFromBackend('DELETE', `/api/v1/predefinedFilters/${selectedId}`)
        .then((response) => {
            if(response.message === "Template deleted") {
                alert("Filter deleted successfully");
                if(window.triggerConfetti) window.triggerConfetti();
            } else {
                console.error(response);
                alert("An error has occured. Look in the browser console for more details.");  
            }
        })
        .catch((error) => {
            console.error(error);
            alert("An error has occured: " + error);
        })
    }

    fetchPredefinedFilterData(filterId) {
        return fetchFromBackend('GET', `/api/v1/predefinedFilters/${filterId}`);
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
        }

        const saveFilterButton = document.getElementById("storeFilterButton");
        if (saveFilterButton) {
            saveFilterButton.addEventListener('click', () => this.storePredefinedFilterInBackend());
        }

        const updateFilterButton = document.getElementById("updateFilterButton");
        if (updateFilterButton) {
            updateFilterButton.addEventListener('click', () => this.updatePredefinedFilterInBackend());
        }

        const deleteFilterButton = document.getElementById("deleteFilterButton");
        if (deleteFilterButton) {
            deleteFilterButton.addEventListener('click', () => this.deletePredefinedFilterFromBackend());
        }

    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    // Initialize everything
    const controller = new FilterUIController($table);
    controller.bindEvents();
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

    return fetch(path, options)
        .then(res => res.json());
}

function fetchItemFromBackendById(type, id) {
    const typeMap = {
        asset: "hardware",
        category: "categories",
        company: "companies",
        location: "locations",
        manufacturer: "manufacturers",
        model: "models",
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
    for(var i = 0; i < fields.length; i++)
    {
        fields[i].disabled = state;
    }
}


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

</script>

<style>
/* 
Base styles for the filter sidebar and its transitions.
Handles showing/hiding the sidebar smoothly.
*/
.filter-sidebar {
    transition: all 0.3s ease;
    position: relative;
}

/* 
Smooth transition for the filter body (the inner panel).
Ensures expanding/collapsing feels fluid.
*/
.filter-body {
    transition: all 0.3s ease;
    overflow: hidden;
}

/* 
Fade-in/out effect for any element with this class when shown/hidden.
*/
.filter-content {
    transition: opacity 0.2s ease;
}

/* 
Fade-in/out for filter section title and clear text.
*/
.filter-title,
.clear-text {
    transition: opacity 0.2s ease;
}

/* ---------- Desktop styles ---------- */
@media (min-width: 769px) {
    /* 
    When collapsed, the sidebar is skinny and its content is hidden.
    */
    .filter-sidebar.collapsed {
        width: 60px;
        min-width: 60px;
    }
    
    .filter-sidebar.collapsed .filter-body {
        padding: 0;
        max-height: 0;
    }
    
    .filter-sidebar.collapsed .filter-content {
        display: none;
    }
    
    .filter-sidebar.collapsed .filter-title,
    .filter-sidebar.collapsed .clear-text {
        display: none;
    }
}

/* ---------- Mobile/Tablet styles ---------- */
@media (max-width: 768px) {
    /* 
    Sidebar takes full width and less margin on mobile.
    */
    .filter-sidebar {
        width: 100% !important;
        margin-bottom: 15px;
    }
    
    /* 
    Collapsed sidebar hides content and disables interaction.
    */
    .filter-sidebar.collapsed .filter-body {
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    
    .filter-sidebar.collapsed .filter-content {
        opacity: 0;
        pointer-events: none;
    }
    
    .filter-sidebar.collapsed .filter-title,
    .filter-sidebar.collapsed .clear-text {
        opacity: 0.7;
    }
}

/* 
Ensures filter panel container uses full width and is padded.
Makes it responsive.
*/
.container {
    width: 100%;
    margin: 0 auto;
    padding: 10px;
    box-sizing: border-box;
}

/* 
Makes the advanced search filters section block-level and full width.
*/
#advancedSearchFilters {
    display: block;
    max-width: 100%;
    margin: 0;
}

/* 
Scrollable filter panel body, with padding.
*/
.box-body {
    overflow-y: auto;
    padding: 15px;
}

/* 
On small screens, limit box-body max height to 75% of viewport.
Makes scrolling manageable on mobile.
*/
@media (max-width: 768px) {
  .box-body {
    max-height: 75vh;
  }
}

/* 
On desktop, take up all available vertical height.
*/
@media (min-width: 769px) {
  .box-body {
    height: 100%;
  }
}

/* 
Button blocks have a little space between each for clarity.
*/
.btn-block {
    margin-bottom: 5px;
}

/* 
Custom thin scrollbar for the filter area.
Aesthetic tweak.
*/
.box-body::-webkit-scrollbar {
    width: 6px;
}

/* 
Collapse button tweaks for spacing.
*/
.collapse-toggle {
    margin-right: 5px;
}

/* 
By default, hide both the desktop and mobile collapse icons.
*/
.icon-desktop,
.icon-mobile {
  display: none;
}

/* 
Show the desktop collapse icon on desktop screens.
*/
@media (min-width: 768px) {
  .icon-desktop {
    display: inline;
  }
}

/* 
Show the mobile collapse icon on mobile screens.
*/
@media (max-width: 767px) {
  .icon-mobile {
    display: inline;
  }
}

</style>