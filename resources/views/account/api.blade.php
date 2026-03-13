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

                 @if (!config('app.lock_passwords'))
                    <livewire:personal-access-tokens />
                 @else
                     <p class="help-block">{{ trans('general.feature_disabled') }}</p>
                @endif
            </div>
            <div class="col-md-4">
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle faa-pulse animated"></i>
                    {{ trans('account/general.api_key_warning') }}
                </div>

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
