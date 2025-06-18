@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/general.software_licenses') }}
@parent
@stop


@section('header_right')
@can('create', \App\Models\License::class)
    <a href="{{ route('licenses.create') }}" accesskey="n" class="btn btn-primary pull-right">
      {{ trans('general.create') }}
    </a>
    @endcan
@can('view', \App\Models\License::class)
    <a class="btn btn-default pull-right" href="{{ route('licenses.export') }}" style="margin-right: 5px;">{{ trans('general.export') }}</a>
@endcan
@stop

{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-body">

          <table
              data-columns="{{ \App\Presenters\LicensePresenter::dataTableLayout() }}"
              data-cookie-id-table="licensesTable"
              data-pagination="true"
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
              id="licensesTable"
              class="table table-striped snipe-table"
              data-url="{{ route('api.licenses.index') }}"
              data-export-options='{
            "fileName": "export-licenses-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>
          </table>

      </div><!-- /.box-body -->

      <div class="box-footer clearfix">
      </div>
    </div><!-- /.box -->
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

@stop
