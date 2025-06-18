<!-- Image stuff - kept in /resources/views/partials/forms/edit/image-upload.blade.php -->
<!-- Image Delete -->
@if (isset($image_path))
    @if (isset($item) && ($item->{($fieldname ?? 'image')}))
        <div class="form-group{{ $errors->has('image_delete') ? ' has-error' : '' }}">
            <div class="col-md-9 col-md-offset-3">
                <label class="form-control">
                    <input type="checkbox" name="image_delete" value="1" @checked(old('image_delete')) aria-label="image_delete">
                    {{ trans('general.image_delete') }}
                    {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
                </label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-8 col-md-offset-3">
                <img src="{{ Storage::disk('public')->url($image_path.e($item->{($fieldname ?? 'image')})) }}" class="img-responsive">
                {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
            </div>
        </div>
    @endif
@endif
<!-- Image Upload and preview -->

<div class="form-group {{ $errors->has((isset($fieldname) ? $fieldname : 'image')) ? 'has-error' : '' }}">
    <label class="col-md-3 control-label" for="{{ (isset($fieldname) ? $fieldname : 'image') }}">{{ trans('general.image_upload') }}</label>
    <div class="col-md-8">

        <input type="file" id="{{ (isset($fieldname) ? $fieldname : 'image') }}" name="{{ (isset($fieldname) ? $fieldname : 'image') }}" aria-label="{{ (isset($fieldname) ? $fieldname : 'image') }}" class="sr-only">

        <label class="btn btn-default" aria-hidden="true">
            {{ trans('button.select_file')  }}
            <input type="file" name="{{ (isset($fieldname) ? $fieldname : 'image') }}" class="js-uploadFile" id="uploadFile" data-maxsize="{{ Helper::file_upload_max_size() }}" accept="image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml,image/avif" style="display:none; max-width: 90%" aria-label="{{ (isset($fieldname) ? $fieldname : 'image') }}" aria-hidden="true">
        </label>
        <span class='label label-default' id="uploadFile-info"></span>

        <p class="help-block" id="uploadFile-status">{{ trans('general.image_filetypes_help', ['size' => Helper::file_upload_max_size_readable()]) }} {{ $help_text ?? '' }}</p>



        {!! $errors->first('image', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
    <div class="col-md-4 col-md-offset-3" aria-hidden="true">
        <img id="uploadFile-imagePreview" style="max-width: 300px; display: none;" alt="{{ trans('general.alt_uploaded_image_thumbnail') }}">
    </div>
</div>

