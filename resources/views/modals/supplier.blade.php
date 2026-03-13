{{-- See snipeit_modals.js for what powers this --}}
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h2 class="modal-title">{{ trans('admin/suppliers/table.create') }}</h2>
        </div>
        <div class="modal-body">
            <form action="{{ route('api.suppliers.store') }}" onsubmit="return false">
                <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                </div>
                <div class="dynamic-form-row">
                   @include('partials.forms.edit.name', [ 'item' => new \App\Models\Supplier(), 'translated_name' => trans('admin/suppliers/table.name')])
                </div>
                <div class="dynamic-form-row">
                    <div class="form-group {{ $errors->has('contact') ? ' has-error' : '' }}">
                        <label for="contact" class="col-md-3 control-label">{{ trans('admin/suppliers/table.contact') }}</label>
                        <div class="col-md-7">
                            <input class="form-control" name="contact" type="text" id="contact" value="{{ old('contact') }}">
                        </div>
                    </div>
                </div>
                <div class="dynamic-form-row">
                    <div class="form-group {{ $errors->has('url') ? ' has-error' : '' }}">
                        <label for="contact" class="col-md-3 control-label">{{ trans('general.url') }}</label>
                        <div class="col-md-7">
                            <input class="form-control" name="url" type="text" id="url" value="{{ old('url') }}">
                        </div>
                    </div>
                </div>
                <div class="dynamic-form-row">
                    @include('partials.forms.edit.phone', [ 'item' => new \App\Models\Supplier(), 'translated_name' => trans('admin/suppliers/table.phone')])
                </div>
                <div class="dynamic-form-row">
                    @include('partials.forms.edit.fax', [ 'item' => new \App\Models\Supplier(), 'translated_name' => trans('admin/suppliers/table.fax')])
                </div>
                <div class="dynamic-form-row">
                    @include('partials.forms.edit.email', [ 'item' => new \App\Models\Supplier(), 'translated_name' => trans('admin/suppliers/table.email')])
                </div>


                <div class="dynamic-form-row">
                    @include ('partials.forms.edit.notes')
                </div>



            </form>
        </div>
        <div class="dynamic-form-row">
            @include('modals.partials.footer')
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
