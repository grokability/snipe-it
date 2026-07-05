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

        <x-table.maintenances
            :route="route('api.maintenances.index').'?completed='.request()->input('completed', 'false').'&upcoming_status='.request()->input('upcoming_status', '')"
        />

        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'maintenances-export', 'search' => true])

<div class="modal fade" id="completeMaintenanceModal" tabindex="-1" role="dialog" aria-labelledby="completeMaintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('button.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="completeMaintenanceModalLabel">{{ trans('admin/maintenances/form.mark_complete') }}</h4>
            </div>
            <form id="completeMaintenanceForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p>{{ trans('admin/maintenances/message.complete.confirm') }}</p>
                    <div class="form-group">
                        <label for="completionNote">{{ trans('admin/maintenances/form.completion_notes') }}</label>
                        <textarea class="form-control" id="completionNote" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    <button type="submit" class="btn btn-success pull-right">{{ trans('admin/maintenances/form.mark_complete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ csrf_token() }}">
    function maintenancesActionsFormatter(value, row) {
        var actions = '<nobr>';

        if ((row.available_actions) && (row.available_actions.update === true)) {
            actions += '<a href="{{ config('app.url') }}/maintenances/' + row.id + '/edit" class="actions btn btn-sm btn-warning hidden-print" data-tooltip="true" title="{{ trans('general.update') }}"><x-icon type="edit" class="fa-fw" /><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
        }

        if ((row.available_actions) && (row.available_actions.complete === true)) {
            actions += '<button type="button" class="actions btn btn-sm btn-success hidden-print complete-maintenance" data-tooltip="true" title="{{ trans('admin/maintenances/form.mark_complete') }}" data-url="{{ config('app.url') }}/maintenances/' + row.id + '/complete"><x-icon type="checkmark" class="fa-fw" /><span class="sr-only">{{ trans('admin/maintenances/form.mark_complete') }}</span></button>&nbsp;';
        } else {
            actions += '<button type="button" class="actions btn btn-sm btn-default hidden-print disabled" disabled data-tooltip="true" title="{{ trans('admin/maintenances/form.already_complete') }}"><x-icon type="checkmark" class="fa-fw" /><span class="sr-only">{{ trans('admin/maintenances/form.already_complete') }}</span></button>&nbsp;';
        }

        if ((row.available_actions) && (row.available_actions.delete === true)) {
            actions += '<a href="{{ config('app.url') }}/maintenances/' + row.id + '" '
                + ' class="actions btn btn-danger btn-sm delete-asset hidden-print" data-tooltip="true" '
                + ' data-toggle="modal" data-icon="fa-trash"'
                + ' data-content="{{ trans('general.sure_to_delete') }}: ' + row.name + '?" '
                + ' data-title="{{ trans('general.delete') }}" onClick="return false;">'
                + '<x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
        }

        actions += '</nobr>';
        return actions;
    }

    $('body').on('click', '.complete-maintenance', function () {
        var url = $(this).data('url');
        $('#completeMaintenanceForm').attr('action', url);
        $('#completionNote').val('');
        $('#completeMaintenanceModal').modal('show');
    });
</script>
@stop
