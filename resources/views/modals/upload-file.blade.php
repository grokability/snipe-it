<!-- Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" role="dialog" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="uploadFileModalLabel">{{ trans('general.file_upload') }}</h4>
            </div>
            <form
                method="POST"
                action="{{ route('ui.files.store', ['object_type' => str_plural($item_type), 'id' => $item_id]) }}"
                accept-charset="UTF-8"
                class="form-horizontal"
                enctype="multipart/form-data"
            >
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="modal-body">

                <x-input.file-upload name="file" :multiple="true" :required="true" />

                <div class="form-group">
                    <label class="col-md-3 control-label" for="notes">{{ trans('general.notes') }}</label>
                    <div class="col-md-8">
                        <x-input.textarea
                            name="notes"
                            id="notes"
                            :value="old('notes')"
                            placeholder="{{ trans('general.notes') }} ({{ trans('general.optional') }})"
                            rows="3"
                            aria-label="{{ trans('general.notes') }}"
                        />
                    </div>
                </div>

            </div> <!-- /.modal-body-->
            <div class="modal-footer">
                <a href="#" class="pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</a>
                <button type="submit" class="btn btn-theme" formnovalidate>{{ trans('button.upload') }}</button>
            </div>
            </form>
        </div>
    </div>
</div>
