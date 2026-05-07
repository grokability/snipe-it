<div class="form-group {{ $errors->has('file.*') ? 'has-error' : '' }}" id="{{ $input_id }}-upload">
    <label class="col-md-3 control-label" for="{{ $input_id }}">
        {{ trans('general.file_upload') }}
    </label>

    <div class="col-md-9">
        <label class="btn btn-sm btn-theme" for="{{ $input_id }}">
            {{ trans('button.select_files') }}
            <input
                type="file"
                name="file[]"
                multiple
                class="js-uploadFile"
                id="{{ $input_id }}"
                data-maxsize="{{ Helper::file_upload_max_size() }}"
                accept="{{ config('filesystems.allowed_upload_mimetypes') }}"
                style="display:none"
                aria-label="file"
                aria-hidden="true"
            >
        </label>

        <span id="{{ $input_id }}-info"></span>
        <p class="help-block" id="{{ $input_id }}-status">{{ trans('general.upload_filetypes_help', ['allowed_filetypes' => config('filesystems.allowed_upload_extensions'), 'size' => Helper::file_upload_max_size_readable()]) }}</p>

        @foreach ($errors->get('file.*') as $messages)
            @foreach ($messages as $message)
                <span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> {{ $message }}</span><br>
            @endforeach
        @endforeach
        
    </div>
</div>

