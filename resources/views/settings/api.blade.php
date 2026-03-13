@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.oauth_title') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-default pull-right">{{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
    @if (!config('app.lock_passwords'))
        <div id="app">
            <livewire:oauth-clients />
        </div>
    @else
        <p class="text-warning"><i class="fas fa-lock" aria-hidden="true"></i> {{ trans('general.feature_disabled') }}</p>
    @endif

@stop

@section('moar_scripts')
@endsection
