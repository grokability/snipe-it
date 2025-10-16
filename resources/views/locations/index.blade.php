@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.locations') }}
@parent
@stop

{{-- Page content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
          @include('partials.locations-bulk-actions')

          <table
                  data-columns="{{ \App\Presenters\LocationPresenter::dataTableLayout() }}"
                  data-cookie-id-table="locationTable"
                  data-id-table="locationTable"
                  data-toolbar="#locationsBulkEditToolbar"
                  data-bulk-button-id="#bulkLocationsEditButton"
                  data-bulk-form-id="#locationsBulkForm"
                  data-side-pagination="server"
                  data-advanced-search="false"
                  data-sort-order="asc"
                  data-buttons="locationButtons"
                  id="locationTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.locations.index', ['company_id'=>e(request('company_id')), 'status' => e(request('status'))]) }}"
                  data-export-options='{
              "fileName": "export-locations-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>
      </div>
    </div>
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'locations-export', 'search' => true])

@stop
