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

<div class="form-group">
<label for="predefined_filters">{{ trans('predefined filter label') }}</label>
<select
    id="predefined_filters"
    class="form-control select"
    data-placeholder="{{ trans('predefined filter placeholder') }}"
>
    <option></option>
    @foreach($predefined_filters as $predefined_filter)
        <option
            value="{{ $predefined_filter->id }}"
            @selected($predefined_filter->is(request()->route()->parameter('predefinedFilter')))
        >
            {{ $predefined_filter->name }}
        </option>
    @endforeach
</select>
</div>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">

                    <div class="row">
                        <div class="col-md-12">

                            @include('partials.asset-bulk-actions', ['status' => Request::get('status')])
                            @include('partials.advanced-search/advanced-search')

                            <table data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                data-cookie-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                                data-id-table="{{ request()->has('status') ? e(request()->input('status')) : '' }}assetsListingTable"
                                data-search-text="{{ e(Session::get('search')) }}" data-side-pagination="server"
                                data-show-footer="true" data-sort-order="asc" data-sort-name="name"
                                data-toolbar="#assetsBulkEditToolbar" data-bulk-button-id="#bulkAssetEditButton"
                                data-bulk-form-id="#assetsBulkForm"
                                data-buttons="assetButtons"
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
                            </span>
                        </div><!-- /.col -->
                    </div><!-- /.row -->

                </div><!-- ./box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('moar_scripts')
    @include('partials.bootstrap-table')

@stop