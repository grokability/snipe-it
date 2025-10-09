<!-- Image stuff - kept in /resources/views/partials/forms/edit/image-upload.blade.php -->
@if (isset($image_path))
    @if (isset($item) && ($item->{($fieldname ?? 'image')}))
        <div class="form-group{{ $errors->has('image_delete') ? ' has-error' : '' }}">
            <div class="col-md-9 col-md-offset-3">

                @if ((isset($cloned_model)) && ($cloned_model->image!=''))
                    <!-- We are cloning a model. Use the cloned image if the user has checked that box -->
                    <input type="hidden" name="clone_image_from_id" value="{{ $cloned_model->id }}" />
                    <label class="form-control">
                        <input type="checkbox" name="use_cloned_image" value="1" @checked(old('use_cloned_image')) aria-label="use_cloned_image" id="use_cloned_image">
                        {{ trans('general.use_cloned_image') }}
                    </label>
                    <p class="help-block">
                        {{ trans('general.use_cloned_image_help') }}
                    </p>

                    {!! $errors->first('use_cloned_image', '<span class="alert-msg">:message</span>') !!}
                @else
                    <!-- Image Delete -->
                    <label class="form-control">
                        <input type="checkbox" name="image_delete" value="1" @checked(old('image_delete')) aria-label="image_delete" id="image_delete">
                        {{ trans('general.image_delete') }}
                        {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
                    </label>
                @endif

            </div>
    </div>

    <!-- existing image -->
    <div class="form-group" id="existing-image">
        <div class="col-md-8 col-md-offset-3">
            <img src="{{ Storage::disk('public')->url($image_path.e($item->{($fieldname ?? 'image')})) }}" class="img-responsive">
            {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
        </div>
    </div>
   @elseif (isset($item) && (isset($item->model)) && ($item->model->image != ''))
        <div class="form-group">
            <div class="col-md-8 col-md-offset-3">
                <p class="help-block">
                    <x-icon type="info-circle" class="text-primary" /> {{ trans('general.use_cloned_no_image_help') }}
                </p>
            </div>
        </div>
   @endif
@endif
<!-- Image Upload and preview -->

<div class="form-group {{ $errors->has((isset($fieldname) ? $fieldname : 'image')) ? 'has-error' : '' }}" id="image-upload">
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

