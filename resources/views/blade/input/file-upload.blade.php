@use('App\Helpers\Helper')

@props([
    'name' => 'file',
    'label' => null,
    'accept' => null,
    'help_text' => null,
    'multiple' => false,
    'required' => false,
])

<div @class(['form-group', 'has-error' => $errors->has($name)])>
    <label class="col-md-3 control-label" for="{{ $name }}">
        {{ $label ?? trans('general.file_upload') }}
    </label>
    <div class="col-md-8">
        <input
            type="file"
            id="{{ $name }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            class="js-uploadFile sr-only"
            data-maxsize="{{ Helper::file_upload_max_size() }}"
            accept="{{ $accept ?? config('filesystems.allowed_upload_mimetypes') }}"
            aria-label="{{ $label ?? trans('general.file_upload') }}"
            @if ($multiple) multiple @endif
            @if ($required) required @endif
        >

        <label class="btn btn-sm btn-theme" for="{{ $name }}" aria-hidden="true">
            {{ $multiple ? trans('button.select_files') : trans('button.select_file') }}
        </label>

        <span class="label label-default" id="{{ $name }}-info"></span>

        <p class="help-block" id="{{ $name }}-status">
            {{ $help_text ?? trans('general.upload_filetypes_help', ['allowed_filetypes' => config('filesystems.allowed_upload_extensions'), 'size' => Helper::file_upload_max_size_readable()]) }}
        </p>

        {!! $errors->first($name, '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
