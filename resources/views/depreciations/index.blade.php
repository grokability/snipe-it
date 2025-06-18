@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.depreciations')}}
@parent
@stop

@section('header_right')
<a href="{{ route('depreciations.create') }}" class="btn btn-primary pull-right">
  {{ trans('general.create') }}</a>
@stop


{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-body">
          <table
                  data-columns="{{ \App\Presenters\DepreciationPresenter::dataTableLayout() }}"
                  data-cookie-id-table="depreciationsTable"
                  data-pagination="true"
                  data-id-table="depreciationsTable"
                  data-search="true"
                  data-search-highlight="true"
                  data-show-print="true"
                  data-side-pagination="server"
                  data-show-columns="true"
                  data-show-export="true"
                  data-show-refresh="true"
                  data-sort-order="asc"
                  id="depreciationsTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.depreciations.index') }}"
                  data-export-options='{
                    "fileName": "export-depreciations-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
          </table>
      </div>
    </div>
  </div> <!-- /.col-md-9-->


  <!-- side address column -->
  <div class="col-md-3">
    <h2>{{ trans('admin/depreciations/general.about_asset_depreciations') }}</h4>
    <p>{{ trans('admin/depreciations/general.about_depreciations') }} </p>
  </div>

</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'depreciations-export', 'search' => true])
@stop
