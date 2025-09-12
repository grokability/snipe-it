{{-- See snipeit_modals.js for what powers this --}}
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h2 class="modal-title">{{ trans('admin/suppliers/table.create') }}</h2>
        </div>
        <div class="modal-body">
            <form action="{{ route('api.suppliers.store') }}" class="js-suppress-submit">
                <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                </div>
                <div class="dynamic-form-row">
                   @include('partials.forms.edit.name', [ 'item' => new \App\Models\Supplier(), 'translated_name' => trans('admin/suppliers/table.name')])
                </div>
            </form>
        </div>
        <div class="dynamic-form-row">
            @include('modals.partials.footer')
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
