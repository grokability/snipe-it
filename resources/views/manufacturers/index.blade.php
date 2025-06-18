@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/manufacturers/table.asset_manufacturers') }} 
@parent
@stop

{{-- Page title --}}
@section('header_right')
  @can('create', \App\Models\Manufacturer::class)
    <a href="{{ route('manufacturers.create') }}" class="btn btn-primary pull-right">
    {{ trans('general.create') }}</a>
  @endcan

  @if (Request::get('deleted')=='true')
    <a class="btn btn-default pull-right" href="{{ route('manufacturers.index') }}" style="margin-right: 5px;">{{ trans('general.show_current') }}</a>
  @else
    <a class="btn btn-default pull-right" href="{{ route('manufacturers.index', ['deleted' => 'true']) }}" style="margin-right: 5px;">
      {{ trans('general.show_deleted') }}</a>
  @endif

@stop

{{-- Page content --}}
@section('content')

  <div class="row">
    <div class="col-md-12">

      <div class="box box-default">
        <div class="box-body">

    @if ($manufacturer_count == 0)

            <form action="{{ route('manufacturers.seed') }}" method="POST">
              {{ csrf_field() }}
            <div class="callout callout-info">
              <p>
                  {{ trans('general.seeding.manufacturers.prompt') }}
                <button class="btn btn-sm btn-primary hidden-print" rel="noopener">
                  {{ trans('general.seeding.manufacturers.button') }}
                </button>
              </p>
            </div>
            </form>

      @else


            <table
              data-columns="{{ \App\Presenters\ManufacturerPresenter::dataTableLayout() }}"
              data-cookie-id-table="manufacturersTable"
              data-pagination="true"
              data-id-table="manufacturersTable"
              data-search="true"
              data-search-highlight="true"
              data-show-print="true"
              data-side-pagination="server"
              data-show-columns="true"
              data-show-fullscreen="true"
              data-show-export="true"
              data-show-refresh="true"
              data-sort-order="asc"
              id="manufacturersTable"
              class="table table-striped snipe-table"
              data-url="{{route('api.manufacturers.index', ['deleted' => (request('deleted')=='true') ? 'true' : 'false' ]) }}"
              data-export-options='{
                "fileName": "export-manufacturers-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
            </table>


  @endif
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </div>
  </div>
@stop

@section('moar_scripts')


  @include ('partials.bootstrap-table')
@stop
