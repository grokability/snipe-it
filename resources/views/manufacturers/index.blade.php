@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/manufacturers/table.asset_manufacturers') }} 
@parent
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
                <x-tables.bulk-actions
                        id_divname='manufacturersBulkEditToolbar'
                        action_route="{{route('manufacturers.bulk.delete')}}"
                        id_formname="manufacturersBulkForm"
                        id_button="bulkManufacturerEditButton"
                        model_name="manufacturer"
                >
                    @can('delete', App\Models\Manufacturer::class)
                        <option>{{trans('general.delete')}}</option>
                    @endcan
                </x-tables.bulk-actions>

            <table
              data-columns="{{ \App\Presenters\ManufacturerPresenter::dataTableLayout() }}"
              data-cookie-id-table="manufacturersTable"
              data-id-table="manufacturersTable"
              data-advanced-search="false"
              data-side-pagination="server"
              data-sort-order="asc"
              id="manufacturersTable"
              {{-- begin stuff for bulk dropdown --}}
              data-toolbar="#manufacturersBulkEditToolbar"
              data-bulk-button-id="#bulkManufacturerEditButton"
              data-bulk-form-id="#manufacturersBulkForm"
              {{-- end stuff for bulk dropdown --}}
              data-buttons="manufacturerButtons"
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
