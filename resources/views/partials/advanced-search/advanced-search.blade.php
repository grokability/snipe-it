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
        <div id="advanced-search-filters" class="filter-content container">
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
    import initAdvancedSearch from '/js/dist/advanced-search.min.js';

    initAdvancedSearch(
        {
            "tableId": "{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable",
            "predefinedFilterId": {{ $predefined_filter_id ?? "null" }},
            "predefinedFilterName": "{{ $predefined_filter_name }}",
        }
    );
</script>
