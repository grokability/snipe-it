@extends('layouts/edit-form', [
    'createText' => trans('admin/manufacturers/table.create') ,
    'updateText' => trans('admin/manufacturers/table.update'),
    'helpTitle' => trans('admin/manufacturers/table.about_manufacturers_title'),
    'helpText' => trans('admin/manufacturers/table.about_manufacturers_text'),
    'formAction' => (isset($item->id)) ? route('manufacturers.update', ['manufacturer' => $item->id]) : route('manufacturers.store'),
])


{{-- Page content --}}
@section('inputFields')

    <!-- Name -->
    <x-form-row
            :label="trans('admin/manufacturers/table.name')"
            :$item
            name="name"
    />

    <!-- URL -->
    <x-form-row
            :label="trans('general.url')"
            :$item
            name="url"
            type="url"
            input_icon="link"
            input_group_addon="left"
            placeholder="https://example.com"
    />



    <!-- Support URL -->
    <x-form-row
            :label="trans('admin/manufacturers/table.support_url')"
            :$item
            name="support_url"
            type="url"
            input_icon="link"
            input_group_addon="left"
            placeholder="https://example.com"
    />



    <!-- Warranty Lookup URL -->
    <x-form-row
            :label="trans('admin/manufacturers/table.warranty_lookup_url')"
            :$item
            name="warranty_lookup_url"
            type="url"
            help_text="{!! trans('admin/manufacturers/message.support_url_help') !!}"
            input_icon="link"
            input_group_addon="left"
            placeholder="https://example.com"
    />

    <!-- Support Phone -->
    <x-form-row
            :label="trans('admin/manufacturers/table.support_phone')"
            :$item
            name="support_phone"
            input_div_class="col-md-6"
            type="tel"
            input_icon="phone"
            input_group_addon="left"
            placeholder="1-800-555-5555"
    />


    <!-- Support Email -->
    <x-form-row
            :label="trans('admin/manufacturers/table.support_email')"
            :$item
            name="support_email"
            input_div_class="col-md-6"
            type="email"
            input_icon="email"
            input_group_addon="left"
            placeholder="support@example.com"
    />


@include ('partials.forms.edit.image-upload', ['image_path' => app('manufacturers_upload_path')])


    <!-- Notes -->
    <x-form-row
            :label="trans('general.notes')"
            :$item
            name="notes"
            type="textarea"
            placeholder="{{ trans('general.placeholders.notes') }}"
    />

    <fieldset name="color-preferences">
        <x-form-legend help_text="{{ trans('general.tag_color_help') }}">
            {{ trans('general.tag_color') }}
        </x-form-legend>
        <!--  color -->
        <div class="form-group {{ $errors->has('tag_color') ? 'error' : '' }}">
            <label for="tag_color" class="col-md-3 control-label">
                {{ trans('general.tag_color') }}
            </label>
            <div class="col-md-9">
                <x-input.colorpicker :item="$item" id="color" :value="old('color', ($item->color ?? '#f4f4f4'))" name="tag_color" id="tag_color" />
                {!! $errors->first('tag_color', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
            </div>
        </div>
    </fieldset>

@stop
