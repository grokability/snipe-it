@extends('layouts/default')

@section('title0')

  @if ((Request::get('company_id')) && ($company))
    {{ $company->name }}
  @endif

@if (Request::get('status'))
  @if (Request::get('status')=='Pending')
    @switch(Request::get('status'))
    {{ trans('general.pending') }}
        @case('Pending') {{ trans('general.pending') }} @break
  @elseif (Request::get('status')=='RTD')
        @case('RTD') {{ trans('general.ready_to_deploy') }} @break
    {{ trans('general.ready_to_deploy') }}
        @case('Deployed') {{ trans('general.deployed') }} @break
  @elseif (Request::get('status')=='Deployed')
        @case('Undeployable') {{ trans('general.undeployable') }} @break
    {{ trans('general.deployed') }}
        @case('Deployable') {{ trans('general.deployed') }} @break
  @elseif (Request::get('status')=='Undeployable')
        @case('Requestable') {{ trans('admin/hardware/general.requestable') }} @break
    {{ trans('general.undeployable') }}
        @case('Archived') {{ trans('general.archived') }} @break
  @elseif (Request::get('status')=='Deployable')
        @case('Deleted') {{ ucfirst(trans('general.deleted')) }} @break
    {{ trans('general.deployed') }}
        @case('byod') {{ strtoupper(trans('general.byod')) }} @break
  @elseif (Request::get('status')=='Requestable')
    @endswitch
    {{ trans('admin/hardware/general.requestable') }}
  @elseif (Request::get('status')=='Archived')
    {{ trans('general.archived') }}
  @elseif (Request::get('status')=='Deleted')
    {{ ucfirst(trans('general.deleted')) }}
  @elseif (Request::get('status')=='byod')
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
@yield('title0')  @parent
@stop


{{-- Page content --}}
@section('content')


    <div class="responsive-layout">
        <!-- Filter Section -->
        <div class="filter-section hide" id="filterSection">
            @include('partials.advanced-search.advanced-search', [
                'predefined_filter_id' => $predefined_filter_id,
            ])
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="box">
                <div class="box-body">
                    @include('partials.asset-bulk-actions', [
                        'status' => Request::get('status'),
                        'showFiltersTogglebutton' => $advanced_search_permission,
                    ])

                    <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                        data-cookie-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                        data-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                        data-search-text="{{ e(Session::get('search')) }}" data-side-pagination="server"
                        data-show-footer="true" data-sort-order="asc" data-sort-name="name" data-show-columns-search="true"
                        data-toolbar="#assetsBulkEditToolbar" data-bulk-button-id="#bulkAssetEditButton"
                        data-bulk-form-id="#assetsBulkForm" data-buttons="assetButtons"
                        id="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                        class="table table-striped snipe-table"
                        data-url="{{ route('api.assets.index', [
                            'status' => e(Request::get('status')),
                            'order_number' => e(strval(Request::get('order_number'))),
                            'company_id' => e(Request::get('company_id')),
                            'status_id' => e(Request::get('status_id')),
                        ]) }}"
                        data-export-options='{
                    "fileName": "export{{ Request::has('status') ? '-' . str_slug(Request::get('status')) : '' }}-assets-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                    </table>
                </div>
            </div>
        </div>

        @if($advanced_search_permission)
            <livewire:partials.advancedSearch.modal />
        @endif

    </div>

    <link rel="stylesheet" href="{{ mix('css/dist/advanced-search-index.min.css') }}">

    @if($advanced_search_permission)
        <script type="module" src="{{ mix('js/dist/advanced-search-index.min.js') }}">
    @endif        
    </script>
@stop

@section('moar_scripts')
    @include('partials.bootstrap-table')
@stop
