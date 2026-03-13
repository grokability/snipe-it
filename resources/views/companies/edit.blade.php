@extends('layouts/edit-form', [
    'createText' => trans('admin/companies/table.create') ,
    'updateText' => trans('admin/companies/table.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.companies'),
    'formAction' => (isset($item->id)) ? route('companies.update', ['company' => $item->id]) : route('companies.store'),
])

{{-- Page content --}}
@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('admin/companies/table.name')])
@include ('partials.forms.edit.phone')
@include ('partials.forms.edit.fax')
@include ('partials.forms.edit.email')
@include ('partials.forms.edit.image-upload', ['image_path' => app('companies_upload_path')])

<div class="form-group{!! $errors->has('notes') ? ' has-error' : '' !!}">
    <label for="notes" class="col-md-3 control-label">{{ trans('general.notes') }}</label>
    <div class="col-md-8">

        <x-input.textarea
                name="notes"
                id="notes"
                :value="old('notes', $item->notes)"
                placeholder="{{ trans('general.placeholders.notes') }}"
                aria-label="notes"
                rows="5"
        />

    </div>
</div>

<fieldset name="color-preferences">
    <x-form.legend help_text="{{ trans('general.tag_color_help') }}">
        {{ trans('general.tag_color') }}
    </x-form.legend>
    <!--  color -->
    <div class="form-group {{ $errors->has('tag_color') ? 'error' : '' }}">
        <label for="tag_color" class="col-md-3 control-label">
            {{ trans('general.tag_color') }}
        </label>
        <div class="col-md-9">
            <x-input.colorpicker :item="$item" id="tag_color" :value="old('tag_color', ($item->tag_color ?? '#f4f4f4'))" name="tag_color" id="tag_color" />
            {!! $errors->first('tag_color', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
        </div>
    </div>
</fieldset>


@stop
