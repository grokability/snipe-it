@use('App\Helpers\Helper')
@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Str')

@props([
    'item' => null,
    'imagePath' => null,
    'fieldname' => 'image',
    'clonedModel' => null,
    'helpText' => null,
])

@php
    $existing_image = $item?->{$fieldname};
    $has_cloned_image = $clonedModel && $clonedModel->image !== '';
@endphp

@if ($imagePath && $existing_image)
    {{-- Delete-existing / reuse-cloned toggle. No label column. The checkbox
         label is inside the input area, so we render a bare offset form-group. --}}
    <div class="form-group {{ $errors->has('image_delete') ? 'has-error' : '' }}">
        <div class="col-md-9 col-md-offset-3">
            @if ($has_cloned_image)
                <input type="hidden" name="clone_image_from_id" value="{{ $clonedModel->id }}" />
                <label class="form-control">
                    <input type="checkbox" name="use_cloned_image" value="1" @checked(old('use_cloned_image')) aria-label="use_cloned_image" id="use_cloned_image">
                    {{ trans('general.use_cloned_image') }}
                </label>
                <p class="help-block">{{ trans('general.use_cloned_image_help') }}</p>
                <x-form.error name="use_cloned_image" />
            @else
                <label class="form-control">
                    <input type="checkbox" name="image_delete" value="1" @checked(old('image_delete')) aria-label="image_delete" id="image_delete">
                    {{ trans('general.image_delete') }}
                    <x-form.error name="image_delete" />
                </label>
            @endif
        </div>
    </div>

    {{-- Existing image thumbnail. Also no label column. --}}
    <div class="form-group" id="existing-image">
        <div class="col-md-8 col-md-offset-3">
            @if (Str::startsWith($existing_image, ['http://', 'https://']))
                <img src="{{ $existing_image }}" class="img-responsive" alt="">
            @else
                <img src="{{ Storage::disk('public')->url($imagePath.e($existing_image)) }}" class="img-responsive" alt="">
            @endif
            <x-form.error name="image_delete" />
        </div>
    </div>
@elseif ($item && isset($item->model) && $item->model->image !== '')
    <div class="form-group">
        <div class="col-md-8 col-md-offset-3">
            <p class="help-block">
                <x-icon type="info-circle" class="text-primary" /> {{ trans('general.use_cloned_no_image_help') }}
            </p>
        </div>
    </div>
@endif

<x-form.row
    :label="trans('general.image_upload')"
    :name="$fieldname"
    id="image-upload"
>
    <x-slot:input>
        <input type="file" id="{{ $fieldname }}" name="{{ $fieldname }}" aria-label="{{ $fieldname }}" class="sr-only">

        <label class="btn btn-sm btn-theme" aria-hidden="true">
            {{ trans('button.select_file') }}
            <input type="file" name="{{ $fieldname }}" class="js-uploadFile" id="uploadFile" data-maxsize="{{ Helper::file_upload_max_size() }}" accept="image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml,image/avif" style="display:none; max-width: 90%" aria-label="{{ $fieldname }}" aria-hidden="true">
        </label>

        <span class="label label-default" id="uploadFile-info"></span>

        <p class="help-block" id="uploadFile-status">
            {{ trans('general.image_filetypes_help', ['size' => Helper::file_upload_max_size_readable()]) }} {{ $helpText }}
        </p>
    </x-slot:input>
</x-form.row>

<div class="form-group">
    <div class="col-md-4 col-md-offset-3" aria-hidden="true">
        <img id="uploadFile-imagePreview" style="max-width: 300px; display: none;" alt="{{ trans('general.alt_uploaded_image_thumbnail') }}">
    </div>
</div>
