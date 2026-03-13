@extends('layouts/edit-form', [
    'createText' =>  trans('admin/kits/general.create'),
    'updateText' => trans('admin/kits/general.update'),
    'formAction' => (isset($item->id)) ? route('kits.update', ['kit' => $item->id]) : route('kits.store'),
])

{{-- Page content --}}
@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('general.name')])
@stop
