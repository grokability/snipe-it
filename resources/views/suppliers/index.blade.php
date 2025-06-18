@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/suppliers/table.suppliers') }}
@parent
@stop

{{-- Page content --}}
@section('content')


@section('header_right')
  @can('create', \App\Models\Supplier::class)
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
  @endcan
@stop

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <table
            data-columns="{{ \App\Presenters\SupplierPresenter::dataTableLayout() }}"
            data-cookie-id-table="suppliersTable"
            data-pagination="true"
            data-id-table="suppliersTable"
            data-search="true"
            data-search-highlight="true"
            data-show-print="true"
            data-side-pagination="server"
            data-show-columns="true"
            data-show-fullscreen="true"
            data-show-export="true"
            data-show-refresh="true"
            data-sort-order="asc"
            id="suppliersTable"
            class="table table-striped snipe-table"
            data-url="{{ route('api.suppliers.index') }}"
            data-export-options='{
            "fileName": "export-suppliers-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>
      </table>
    </div>
  </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'suppliers-export', 'search' => true])
@stop
