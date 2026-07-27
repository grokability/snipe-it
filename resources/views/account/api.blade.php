@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('account/general.personal_api_keys') }}
    @parent
@stop
{{-- Page content --}}
@section('content')
        <div class="row">
            <div class="col-md-8">

                @if (! config('app.lock_passwords'))
                    <livewire:personal-access-tokens />
                @else
                    <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
                @endif
            </div>
            <div class="col-md-4">
                <x-alert type="warning" icon="warning">
                    {{ trans('account/general.api_key_warning') }}
                </x-alert>

                <p>{{ trans('account/general.api_base_url') }}<br>
                    <code>{{ url('/api/v1') }}{!! trans('account/general.api_base_url_endpoint') !!}</code></p>

                <p>{{ trans('account/general.api_token_expiration_time') }}
                    <strong>{{ config('passport.expiration_years') }} {{ trans('general.years') }} </strong>.</p>


                <p>{!! trans('account/general.api_reference') !!}</p>
            </div>
        </div>

@stop

@section('moar_scripts')
@endsection
