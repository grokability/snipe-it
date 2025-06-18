@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/asset_maintenances/general.asset_maintenances') }}
  @parent
@stop


@section('header_right')
  @can('update', \App\Models\Asset::class)
    <a href="{{ route('maintenances.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
  @endcan
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">

          <table
              data-columns="{{ \App\Presenters\AssetMaintenancesPresenter::dataTableLayout() }}"
              data-cookie-id-table="maintenancesTable"
              data-pagination="true"
              data-search-highlight="true"
              data-search="true"
              data-show-print="true"
              data-side-pagination="server"
              data-show-columns="true"
              data-show-fullscreen="true"
              data-show-footer="true"
              data-show-export="true"
              data-show-refresh="true"
              id="maintenancesTable"
              class="table table-striped snipe-table"
              data-url="{{route('api.maintenances.index') }}"
              data-export-options='{
                "fileName": "export-maintenances-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>

        </table>

      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'maintenances-export', 'search' => true])
<script nonce="{{ csrf_token() }}">
    function maintenanceActions(value, row) {
        var actions = '<nobr>';
        if ((row) && (row.available_actions.update === true)) {
            actions += '<a href="{{ config('app.url') }}/hardware/maintenances/' + row.id + '/edit" class="btn btn-sm btn-warning" data-tooltip="true" title="Update"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
        }
        actions += '</nobr>'
        if ((row) && (row.available_actions.delete === true)) {
            actions += '<a href="{{ config('app.url') }}/hardware/maintenances/' + row.id + '" '
                + ' class="btn btn-danger btn-sm delete-asset"  data-tooltip="true"  '
                + ' data-toggle="modal" '
                + ' data-content="{{ trans('general.sure_to_delete') }} ' + row.name + '?" '
                + ' data-title="{{  trans('general.delete') }}" onClick="return false;">'
                + '<i class="fas fa-trash"></i></a></nobr>';
        }

        return actions;
    }

</script>
@stop
