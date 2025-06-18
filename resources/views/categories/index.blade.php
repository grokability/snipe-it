@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.categories') }}
@parent
@stop


@section('header_right')
<a href="{{ route('categories.create') }}" class="btn btn-primary pull-right">
  {{ trans('general.create') }}</a>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <table
            data-columns="{{ \App\Presenters\CategoryPresenter::dataTableLayout() }}"
            data-cookie-id-table="categoryTable"
            data-pagination="true"
            data-id-table="categoryTable"
            data-search="true"
            data-search-highlight="true"
            data-show-print="true"
            data-side-pagination="server"
            data-show-columns="true"
            data-show-fullscreen="true"
            data-show-export="true"
            data-show-refresh="true"
            data-sort-order="asc"
            id="categoryTable"
            class="table table-striped snipe-table"
            data-url="{{ route('api.categories.index') }}"
            data-export-options='{
              "fileName": "export-categories-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
          </table>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>

@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table',
      ['exportFile' => 'category-export',
      'search' => true,
      'columns' => \App\Presenters\CategoryPresenter::dataTableLayout()
  ])
@stop

