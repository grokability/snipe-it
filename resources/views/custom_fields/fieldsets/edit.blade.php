@extends('layouts/edit-form', [
    'createText' => trans('admin/custom_fields/general.create_fieldset') ,
    'updateText' => trans('admin/custom_fields/general.update_fieldset'),
    'helpText' => trans('admin/custom_fields/general.about_fieldsets_text'),
    'helpPosition' => 'right',
    'formAction' => (isset($item->id)) ? route('fieldsets.update', ['fieldset' => $item->id]) : route('fieldsets.store'),
])

@section('content')
    @parent
@stop

@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('general.name')])
    <input type="hidden" name="tab" value="{{ Request::query('tab') }}" />
@stop


