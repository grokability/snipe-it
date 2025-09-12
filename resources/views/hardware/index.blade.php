@extends('layouts/default')

@section('title0')

@if (Request::get('company_id') && $company)
    {{ $company->name }}
@endif

@if (Request::get('status'))
    @if (Request::get('status') == 'Pending')
        {{ trans('general.pending') }}
    @elseif (Request::get('status') == 'RTD')
        {{ trans('general.ready_to_deploy') }}
    @elseif (Request::get('status') == 'Deployed')
        {{ trans('general.deployed') }}
    @elseif (Request::get('status') == 'Undeployable')
        {{ trans('general.undeployable') }}
    @elseif (Request::get('status') == 'Deployable')
        {{ trans('general.deployed') }}
    @elseif (Request::get('status') == 'Requestable')
        {{ trans('admin/hardware/general.requestable') }}
    @elseif (Request::get('status') == 'Archived')
        {{ trans('general.archived') }}
    @elseif (Request::get('status') == 'Deleted')
        {{ ucfirst(trans('general.deleted')) }}
    @elseif (Request::get('status') == 'byod')
        {{ strtoupper(trans('general.byod')) }}
    @endif
@else
    {{ trans('general.all') }}
@endif
{{ trans('general.assets') }}

@if (Request::has('order_number'))
    : Order #{{ strval(Request::get('order_number')) }}
@endif
@stop

{{-- Page title --}}
@section('title')
@yield('title0') @parent
@stop

{{-- Page content --}}
@section('content')

<div class="responsive-layout">
    <!-- Filter Section - responsive positioning -->
    <div class="filter-section col-md-3 col-sm-12">
        @include('partials.advanced-search/advanced-search')
    </div>
    
    <!-- Table Section -->
    <div class="table-section col-md-9 col-sm-12">
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        @include('partials.asset-bulk-actions', ['status' => Request::get('status')])

                        <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                            data-cookie-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                            data-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                            data-search-text="{{ e(Session::get('search')) }}" data-side-pagination="server"
                            data-show-footer="true" data-sort-order="asc" data-sort-name="name"
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
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- ./box-body -->
        </div><!-- /.box -->
    </div>
</div>

<style>
.responsive-layout {
    display: flex;
    flex-wrap: wrap;
}

/* Desktop: side-by-side layout */
@media (min-width: 769px) {
    .responsive-layout {
        flex-direction: row;
    }
    
    .filter-section {
        flex: 0 0 25%;
        max-width: 25%;
        padding-right: 15px;
    }
    
    .table-section {
        flex: 0 0 75%;
        max-width: 75%;
    }
}

/* Mobile/Tablet: stacked layout */
@media (max-width: 768px) {
    .responsive-layout {
        flex-direction: column;
    }
    
    .filter-section,
    .table-section {
        flex: 0 0 100%;
        max-width: 100%;
        padding: 0 15px;
    }
    
    .filter-section {
        order: 1; /* Filters on top */
        margin-bottom: 15px;
    }
    
    .table-section {
        order: 2; /* Table below filters */
    }
}
</style>
@stop

@section('moar_scripts')
@include('partials.bootstrap-table')
@stop