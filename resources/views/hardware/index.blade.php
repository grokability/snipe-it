@extends('layouts/default')

@section('title0')
@if (Request::get('company_id') && $company)
    {{ $company->name }}
@endif

@if (Request::get('status'))
    @switch(Request::get('status'))
        @case('Pending') {{ trans('general.pending') }} @break
        @case('RTD') {{ trans('general.ready_to_deploy') }} @break
        @case('Deployed') {{ trans('general.deployed') }} @break
        @case('Undeployable') {{ trans('general.undeployable') }} @break
        @case('Deployable') {{ trans('general.deployed') }} @break
        @case('Requestable') {{ trans('admin/hardware/general.requestable') }} @break
        @case('Archived') {{ trans('general.archived') }} @break
        @case('Deleted') {{ ucfirst(trans('general.deleted')) }} @break
        @case('byod') {{ strtoupper(trans('general.byod')) }} @break
    @endswitch
@else
    {{ trans('general.all') }}
@endif
{{ trans('general.assets') }}

@if (Request::has('order_number'))
    : Order #{{ strval(Request::get('order_number')) }}
@endif
@stop

@section('title')
@yield('title0') @parent
@stop

@section('content')





<div class="responsive-layout">
    <!-- Filter Section -->
    <div class="filter-section hide" id="filterSection">
        @include('partials.advanced-search.advanced-search')
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="box">
            <div class="box-body">
                @include('partials.asset-bulk-actions', ['status' => Request::get('status'), 'showFiltersTogglebutton' => true])

                <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                    data-cookie-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                    data-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                    data-search-text="{{ e(Session::get('search')) }}" data-side-pagination="server"
                    data-show-footer="true" data-sort-order="asc" data-sort-name="name"
                    data-show-columns-search="true"
                data-toolbar="#assetsBulkEditToolbar" data-bulk-button-id="#bulkAssetEditButton"
                    data-bulk-form-id="#assetsBulkForm" data-buttons="assetButtons"
                    id="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                    class="table table-striped snipe-table" data-url="{{ route('api.assets.index', [
    'status' => e(Request::get('status')),
    'order_number' => e(strval(Request::get('order_number'))),
    'company_id' => e(Request::get('company_id')),
    'status_id' => e(Request::get('status_id')),
]) }}" data-export-options='{
                    "fileName": "export{{ Request::has('status') ? '-' . str_slug(Request::get('status')) : '' }}-assets-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* 
Layout container for the whole page.
Uses flexbox so the filter and table sections can sit side by side on desktop, and stack on mobile.
*/
.responsive-layout {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
}

/* 
The filter (sidebar) section.
Transition allows smooth showing/hiding.
*/
.filter-section {
    transition: all 0.3s ease;
}

/* 
When .hide is applied, the filter section is hidden.
!important ensures it's forced, even if overridden by other classes.
*/
.filter-section.hide {
    display: none !important;
}

/* ---------- DESKTOP Styles (screen ≥ 768px) ---------- */
@media (min-width: 768px) {
    /* 
    Filter sidebar gets 25% width, and some space on the right.
    */
    .filter-section {
        flex: 0 0 25%;
        max-width: 25%;
        padding-right: 15px;
    }

    /* 
    Main table takes the remaining 75%.
    */
    .table-section {
        flex: 0 0 75%;
        max-width: 75%;
    }

    /* 
    If filter is hidden, the table takes full width.
    */
    .filter-section.hide + .table-section {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* ---------- MOBILE Styles (screen < 768px) ---------- */
@media (max-width: 767px) {
    /* 
    Filter takes full width, and sits above the table section.
    */
    .filter-section {
        width: 100%;
        margin-bottom: 15px;
    }

    .table-section {
        width: 100%;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleFilterBtn');
        const toggleSidebarButton = document.getElementById('closeSidebarButton');
        const filterSection = document.getElementById('filterSection');

        toggleBtn.addEventListener('click', function () {
            filterSection.classList.toggle('hide');
            updateFilterToggleButtonText(filterSection, toggleBtn);
        });
        toggleSidebarButton.addEventListener('click', function () {
            filterSection.classList.toggle('hide');
            updateFilterToggleButtonText(filterSection, toggleBtn);
        });
    });

    function updateFilterToggleButtonText(filterSection, toggleBtn) {
        const textSpan = toggleBtn.querySelector('.filter-btn-text');

        if (filterSection.classList.contains('hide')) {
            textSpan.innerText = "{{ trans('general.open_filters') }}";
            hideFloatingButtons();
        } else {
            textSpan.innerText = "{{ trans('general.close_filters') }}";
            showFloatingButtons();
            alignFloatingButtons();
        }
    }

</script>

@stop

@section('moar_scripts')
@include('partials.bootstrap-table')
@stop
