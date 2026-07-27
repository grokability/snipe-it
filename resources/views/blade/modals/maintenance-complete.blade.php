{{-- Confirmation modal for marking a maintenance complete. Rendered on any
     page that lists maintenances via `x-table.maintenances`. The bootstrap-
     table actions formatter that emits the green checkmark button lives in
     partials/bootstrap-table.blade.php; the click handler that populates the
     form action and shows this modal lives in resources/assets/js/snipeit.js
     as a delegated `.complete-maintenance` handler. --}}
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
