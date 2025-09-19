@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/predefinedFilters/table.title') }}  {{-- TODO --}}
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-body">
            <table
                    data-columns="{{ \App\Presenters\PredefinedFilterPresenter::dataTableLayout() }}"
                    data-cookie-id-table="predefinedFiltersTable"
                    data-id-table="predefinedFiltersTable"
                    data-show-footer="false"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    data-sort-name="name"
                    id="predefinedFiltersTable"
                    data-buttons="predefinedFiltersButtons"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.predefined-filters.index') }}"
                    data-export-options='{
                "fileName": "export-predefinedFilters-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
          </table>
      </div>
    </div>
  </div>
  <!-- side address column -->
  <div class="col-md-3">
    <h2>{{ trans('admin/predefinedfilters/table.about') }}</h2> {{-- TODO --}}

      <div class="box">
          <div class="box-body">
              <p>{!!  trans('admin/predefinedfilters/table.info') !!}</p> {{-- TODO --}}
          </div>
      </div>


      <div class="box box-success">
          <div class="box-body">
          <p><i class="fas fa-circle text-green"></i> <strong>{{ trans('admin/predefinedfilters/table.deployable') }}</strong>: {!!  trans('admin/predefinedfilters/message.help.deployable')  !!}</p> {{-- TODO --}}
          </div>
      </div>

      <div class="box box-warning">
          <div class="box-body">
              <p><i class="fas fa-circle text-orange"></i> <strong>{{ trans('admin/predefinedfilters/table.pending') }}</strong>: {{ trans('admin/predefinedfilters/message.help.pending') }}</p> {{-- TODO --}}
          </div>
      </div>
      <div class="box box-danger">
          <div class="box-body">
            <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/predefinedfilters/table.undeployable') }}</strong>: {{ trans('admin/predefinedfilters/message.help.undeployable') }}</p> {{-- TODO --}}
          </div>
      </div>

      <div class="box box-danger">
          <div class="box-body">
              <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/predefinedfilters/table.archived') }}</strong>: {{ trans('admin/predefinedfilters/message.help.archived') }}</p> {{-- TODO --}}
          </div>
      </div>

  </div>

</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')


<script nonce="{{ csrf_token() }}">
    function predefinedFiltersFormatter(value, row, index) {
        if (typeof value === 'boolean') {
            return value ? '✔️ Public' : '❌ Private'; // TODO look for matching checkmark
        }
        
        if (typeof value === 'object') {
            return JSON.stringify(value);
        }

        return value ?? '';
    }

@stop
