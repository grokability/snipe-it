@extends('layouts/default')

@section('title0')

  @if ((Request::get('company_id')) && ($company))
    {{ $company->name }}
  @endif



@if (Request::get('status'))
  @if (Request::get('status')=='Pending')
    {{ trans('general.pending') }}
  @elseif (Request::get('status')=='RTD')
    {{ trans('general.ready_to_deploy') }}
  @elseif (Request::get('status')=='Deployed')
    {{ trans('general.deployed') }}
  @elseif (Request::get('status')=='Undeployable')
    {{ trans('general.undeployable') }}
  @elseif (Request::get('status')=='Deployable')
    {{ trans('general.deployed') }}
  @elseif (Request::get('status')=='Requestable')
    {{ trans('admin/hardware/general.requestable') }}
  @elseif (Request::get('status')=='Archived')
    {{ trans('general.archived') }}
  @elseif (Request::get('status')=='Deleted')
    {{ trans('general.deleted') }}
  @elseif (Request::get('status')=='byod')
    {{ trans('general.byod') }}
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

@section('header_right')
  <a href="{{ route('reports/custom') }}" style="margin-right: 5px;" class="btn btn-default">
    {{ trans('admin/hardware/general.custom_export') }}</a>
  @can('create', \App\Models\Asset::class)
  <a href="{{ route('hardware.create') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "n" : ''}} class="btn btn-primary pull-right"></i> {{ trans('general.create') }}</a>
  @endcan
@stop

@section('content')

<div class="row">
  <div class="col-md-12">

    <div class="box box-default">
      <div class="box-body">

        <div class="table-responsive">

          <div class="row">
            <div class="col-md-12">
              @if (Request::get('status'))
                @include('partials.asset-bulk-actions', ['status' => Request::get('status')])
                   
              <table
                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                data-cookie-id-table="{{ request()->has('status') ? e(request()->input('status')) : ''  }}assetsListingTable"
                data-id-table="{{ request()->has('status') ? e(request()->input('status')) : ''  }}assetsListingTable"
                data-search-text="{{ e(Session::get('search')) }}"
                data-side-pagination="server"
                data-show-columns="true"
                data-show-export="true"
                data-show-footer="true"
                data-show-refresh="true"
                data-sort-order="asc"
                data-sort-name="name"
                data-toolbar="#assetsBulkEditToolbar"
                data-bulk-button-id="#bulkAssetEditButton"
                data-bulk-form-id="#assetsBulkForm"
                id="{{ request()->has('status') ? e(request()->input('status')) : ''  }}assetsListingTable"
                class="table table-striped snipe-table"
                data-url="{{ route('api.assets.index',
                    array('status' => e(Request::get('status')),
                    'order_number'=>e(Request::get('order_number')),
                    'company_id'=>e(Request::get('company_id')),
                    'status_id'=>e(Request::get('status_id')))) }}"
                data-export-options='{
                "fileName": "export-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              </table>
              @else
                @include('partials.asset-bulk-actions')
                   
              <table
                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                data-cookie-id-table="assetsListingTable"
                data-id-table="assetsListingTable"
                data-search-text="{{ e(Session::get('search')) }}"
                data-side-pagination="server"
                data-show-columns="true"
                data-show-export="true"
                data-show-footer="true"
                data-show-refresh="true"
                data-sort-order="asc"
                data-sort-name="name"
                data-toolbar="#assetsBulkEditToolbar"
                data-bulk-button-id="#bulkAssetEditButton"
                data-bulk-form-id="#assetsBulkForm"
                id="assetsListingTable"
                class="table table-striped snipe-table"
                data-url="{{ route('api.assets.index',
                    array('status' => e(Request::get('status')),
                    'order_number'=>e(Request::get('order_number')),
                    'company_id'=>e(Request::get('company_id')),
                    'status_id'=>e(Request::get('status_id')))) }}"
                data-export-options='{
                "fileName": "export-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              </table>
              @endif
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.table-responsive -->
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>

@stop

@section('moar_scripts')
@include('partials.bootstrap-table')

{{-- Custom Purchase Order Formatter --}}
<script>
// Custom formatter for Purchase Order column
function purchaseOrderLinkFormatter(value, row) {
    if (value && value.po_number) {
        return '<a href="/purchase-orders/' + value.id + '" class="btn btn-xs btn-primary">' + 
               value.po_number + '</a>';
    } else {
        return '<span class="text-muted">No PO</span>';
    }
}
</script>

@stop
