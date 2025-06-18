@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.components') }}
@parent
@stop

@section('header_right')
  @can('create', \App\Models\Component::class)
    <a href="{{ route('components.create') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=n" : ''}} class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
  @endcan
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
                data-pagination="true"
                data-id-table="componentsTable"
                data-search="true"
                data-search-highlight="true"
                data-show-print="true"
                data-side-pagination="server"
                data-footer-style="footerStyle"
                data-show-columns="true"
                data-show-fullscreen="true"
                data-show-export="true"
                data-show-footer="true"
                data-show-refresh="true"
                data-sort-order="asc"
                data-sort-name="name"
                id="componentsTable"
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
