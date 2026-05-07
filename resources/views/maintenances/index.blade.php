@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/maintenances/general.asset_maintenances') }}
  @parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>

        <x-table
            name="maintenances"
            fixed_right_number="1"
            buttons="maintenanceButtons"
                api_url="{{ route('api.maintenances.index') }}"
                :presenter="\App\Presenters\MaintenancesPresenter::dataTableLayout()"
                export_filename="export-maintenances-{{ date('Y-m-d') }}"
            />

        </x-box>
    </x-container>
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
