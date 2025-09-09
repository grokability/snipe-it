@extends('layouts/edit-form', [
    'createText' => trans('admin/models/table.create') ,
    'updateText' => trans('admin/models/table.update'),
    'topSubmit' => true,
    'helpPosition' => 'right',
    'helpText' => trans('admin/models/general.about_models_text'),
    'formAction' => (isset($item->id)) ? route('models.update', ['model' => $item->id]) : route('models.store'),
])

{{-- Page content --}}
@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('admin/models/table.name'), 'required' => 'true'])
@include ('partials.forms.edit.category-select', ['translated_name' => trans('admin/categories/general.category_name'), 'fieldname' => 'category_id', 'required' => 'true', 'category_type' => 'asset'])
@include ('partials.forms.edit.manufacturer-select', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id'])
@include ('partials.forms.edit.model_number')
@include ('partials.forms.edit.depreciation')
@include ('partials.forms.edit.minimum_quantity')

<!-- require serial boolean -->
<div class="form-group">
    <label for="require_serial" class="col-md-3 control-label">
        {{ trans('admin/hardware/general.require_serial') }}
    </label>

    <div class="col-md-9">
        <div class="form-inline" style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="require_serial" value="1" @checked(old('require_serial', $item->require_serial)) id="require_serial" aria-label="require_serial" />
            <a
                    href="#"
                    data-tooltip="true"
                    title="{{ trans('admin/hardware/general.require_serial_help') }}"
                    style="display: inline-flex; align-items: center;"
            >
                <x-icon type="info-circle" />
                <span class="sr-only">{{ trans('admin/hardware/general.require_serial_help') }}</span>
            </a>
        </div>
    </div>
</div>
<!-- EOL -->

<div class="form-group {{ $errors->has('eol') ? ' has-error' : '' }}">
    <label for="eol" class="col-md-3 control-label">{{ trans('general.eol') }}</label>
    <div class="col-md-3 col-sm-4 col-xs-7">
        <div class="input-group">
            <input class="form-control" type="text" name="eol" id="eol" value="{{ old('eol', isset($item->eol)) ? $item->eol : ''  }}" />
            <span class="input-group-addon">
                {{ trans('general.months') }}
            </span>
        </div>
    </div>
    <div class="col-md-9 col-md-offset-3">
        {!! $errors->first('eol', '<span class="alert-msg" aria-hidden="true"><br><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Custom Fieldset -->
<!-- If $item->id is null we are cloning the model and we need the $model_id variable -->
@livewire('custom-field-set-default-values-for-model', ["model_id" => $item->id ?? $model_id ?? null])

@include ('partials.forms.edit.notes')
@include ('partials.forms.edit.requestable', ['requestable_text' => trans('admin/models/general.requestable')])
@include ('partials.forms.edit.image-upload', ['image_path' => app('models_upload_path')])


@stop
