{{-- See snipeit_modals.js for what powers this --}}
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">{{ trans('admin/kits/general.append_model') }}</h4>
        </div>
        <div class="modal-body">
            <form action="{{ route('api.kits.models.store', $kitId) }}" onsubmit="return false">
                {{ csrf_field() }}
                <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                </div>
                
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-model_id">{{ trans('general.asset_model') }}:
                        </label></div>
                    <div class="col-md-8 col-xs-12 required">
                        <select class="js-data-ajax" data-endpoint="models" name="model" style="width: 100%" id="modal-model_id"></select>
                    </div>
                </div>

                <div class="dynamic-form-row">
                        <div class="col-md-4 col-xs-12"><label for="modal-quantity_id">{{ trans('general.quantity') }}:
                            </label></div>
                        <div class="col-md-8 col-xs-12 required">
                            <input type='text' name='quantity' id='modal-quantity_id' class="form-control" value="1">
                        </div>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
            <button type="button" class="btn btn-primary" id="modal-save">{{ trans('general.save') }}</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
