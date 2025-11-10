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

@include ('partials.advanced-search.advanced-search-translations')
@include('partials.confetti-js', ['autostart' => false])

<script type="module">
import ApiService from '/js/dist/apiService.min.js';
import FilterFormManager from '/js/dist/filterFormManager.min.js';
import FilterUIController from '/js/dist/filterUiController.min.js';
import FloatingButtons from '/js/dist/floating-buttons.min.js';
import { container } from '/js/dist/simpleDIContainer.min.js';

// Add needed stuff into the DI container
container.register("apiService", new ApiService());
container.register("filterFormManager", new FilterFormManager());
container.register("floatingButtons", new FloatingButtons());

document.addEventListener('livewire:init', function () {
    const tableId = "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable";
    const $table = $('#' + tableId);

    const controller = new FilterUIController($table);
    container.register("filterUiController", controller);
    controller.bindEvents();


    (async () => {
        setTimeout(async () => {
            const filterSection = document.getElementById('filterSection');
            filterSection.classList.remove('hide');
            container.resolve("filterFormManager").clearAll();

            await new Promise(resolve => setTimeout(resolve, 0));
            filterSection.classList.add('hide');
        }, 0);
    })();


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
