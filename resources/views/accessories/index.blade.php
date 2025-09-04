@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.accessories') }}
@parent
@stop


{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">

    <div class="box box-default">
      <div class="box-body">

            <table
                data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                data-cookie-id-table="accessoriesTable"
                data-id-table="accessoriesTable"
                data-side-pagination="server"
                data-show-footer="true"
                data-sort-order="asc"
                data-footer-style="footerStyle"
                id="accessoriesTable"
                data-buttons="accessoryButtons"
                class="table table-striped snipe-table"
                data-url="{{route('api.accessories.index') }}"
                data-export-options='{
                    "fileName": "export-accessories-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
          </table>
      </div>
    </div>
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
