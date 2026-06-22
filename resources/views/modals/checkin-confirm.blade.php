{{-- See snipeit_modals.js for what powers this --}}
<div class="modal fade" id="checkin-confirm-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title">{{ trans('general.checkin_confirm_title') }}</h2>
            </div>
            <div class="modal-body">
                {!! nl2br(Helper::parseEscapedMarkedown($setting->checkin_confirm_text)) !!}
                <label class="form-control">
                    <input type="checkbox" id="checkin-confirm" name="checkin-confirm" aria-label="checkin-confirm" required/>
                    {!! nl2br(Helper::parseEscapedMarkedown($setting->checkin_confirm_checkbox_text)) !!}
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="checkin-confirm-ok" disabled>{{ trans('general.checkin') }}</button>
            </div>
        </div><!-- /.modal-content --> 
    </div><!-- /.modal-dialog --> 
</div>
