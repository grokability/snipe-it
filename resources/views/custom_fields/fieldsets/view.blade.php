@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $custom_fieldset->name ?? trans('admin/custom_fields/general.fieldsets') }}
    @parent
@stop


@section('content')
    <x-container>
        {{-- Single component for both create + edit. In create mode it
             shows just the name prompt. --}}
        @if (isset($custom_fieldset) && $custom_fieldset->id)
            @can('update', $custom_fieldset)
                <livewire:fieldset-field-picker :fieldset="$custom_fieldset"/>
            @endcan
        @else
            @can('create', \App\Models\CustomFieldset::class)
                <livewire:fieldset-field-picker/>
            @endcan
        @endif
    </x-container>
@stop
