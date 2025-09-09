@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.components') }}
@parent
@stop


{{-- Page content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <table
                data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                data-cookie-id-table="componentsTable"
                data-id-table="componentsTable"
                data-side-pagination="server"
                data-footer-style="footerStyle"
                data-show-footer="true"
                data-sort-order="asc"
                data-sort-name="name"
                id="componentsTable"
                data-buttons="componentButtons"
                class="table table-striped snipe-table"
                data-url="{{ route('api.components.index') }}"
                data-export-options='{
                "fileName": "export-components-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
        </table>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'components-export', 'search' => true, 'showFooter' => true, 'columns' => \App\Presenters\ComponentPresenter::dataTableLayout()])



@stop
