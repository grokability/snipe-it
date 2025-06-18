@extends('layouts/default')

{{-- Page title --}}
@section('title')

  @if (Request::get('status')=='deleted')
    {{ trans('admin/models/general.view_deleted') }}
    {{ trans('admin/models/table.title') }}
    @else
    {{ trans('admin/models/general.view_models') }}
  @endif
@parent
@stop

{{-- Page title --}}
@section('header_right')
  @can('create', \App\Models\AssetModel::class)
    <a href="{{ route('models.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
  @endcan

  @if (Request::get('status')=='deleted')
    <a class="btn btn-default pull-right" href="{{ route('models.index') }}" style="margin-right: 5px;">{{ trans('admin/models/general.view_models') }}</a>
  @else
    <a class="btn btn-default pull-right" href="{{ route('models.index', ['status' => 'deleted']) }}" style="margin-right: 5px;">{{ trans('admin/models/general.view_deleted') }}</a>
  @endif

@stop


{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">

        @include('partials.models-bulk-actions')
                <table
                        data-columns="{{ \App\Presenters\AssetModelPresenter::dataTableLayout() }}"
                        data-cookie-id-table="asssetModelsTable"
                        data-pagination="true"
                        data-id-table="asssetModelsTable"
                        data-search="true"
                        data-search-highlight="true"
                        data-show-print="true"
                        data-show-footer="true"
                        data-side-pagination="server"
                        data-footer-style="footerStyle"
                        data-show-columns="true"
                        data-toolbar="#modelsBulkEditToolbar"
                        data-bulk-button-id="#bulkModelsEditButton"
                        data-bulk-form-id="#modelsBulkForm"
                        data-show-export="true"
                        data-show-refresh="true"
                        data-sort-order="asc"
                        id="asssetModelsTable"
                        class="table table-striped snipe-table"
                        data-url="{{ route('api.models.index', ['status' => request('status')]) }}"
                        data-export-options='{
              "fileName": "export-models-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
              </table>
        </div>
        </div>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'models-export', 'search' => true])

@stop
