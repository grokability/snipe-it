@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.webhook_title') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop
{{-- Page content --}}
@section('content')
    @livewire('slack-settings-form')
@stop






