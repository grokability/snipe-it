@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('account/general.personal_api_keys') }}
    @parent
@stop
{{-- Page content --}}
@section('content')
    <x-container>

        <x-page-column class="col-md-9">
                @if (! config('app.lock_passwords'))
                <livewire:personal-access-tokens/>
                @else
                <x-box style="height: 300px">
                    {{ trans('general.feature_disabled') }}
                </x-box>
                @endif
        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box>
                <x-slot:header>
                    <x-icon type="oauth"/> {{ trans('account/general.personal_api_keys') }}
                </x-slot:header>

                <x-alert type="warning" icon="warning">
                    {{ trans('account/general.api_key_warning') }}
                </x-alert>

                <ul class="list-group list-group-unbordered">


                    <x-info-element>
                        <strong>{{ trans('account/general.api_base_url') }}</strong>
                    </x-info-element>

                    <x-info-element icon_type="code" title="API">
                        <x-copy-to-clipboard class="pull-right" copy_what="API">
                            <code>{{ url('/api/v1') }}{!! trans('account/general.api_base_url_endpoint') !!}</code>
                        </x-copy-to-clipboard>
                    </x-info-element>


                    <x-info-element icon_type="docs" title="Documentation">
                        <a href="https://snipe-it.readme.io/reference/api-overview" target="_blank">https://snipe-it.readme.io/docs/mcp-server
                            <x-icon type="external-link" class="fa-fw pull-right"/>
                        </a>
                    </x-info-element>

                    <x-info-element>
                        <strong>{{ trans('account/general.mcp_url') }}</strong>
                    </x-info-element>

                    <x-info-element icon_type="mcp" title="MCP">
                        <x-copy-to-clipboard class="pull-right" copy_what="MCP">
                            <code>{{ url('/mcp/snipe-it') }}</code>
                        </x-copy-to-clipboard>
                    </x-info-element>

                    <x-info-element icon_type="docs" title="Documentation">
                        <a href="https://snipe-it.readme.io/docs/mcp-server" target="_blank">https://snipe-it.readme.io/docs/mcp-server
                            <x-icon type="external-link" class="fa-fw pull-right"/>
                        </a>
                    </x-info-element>

                    <x-info-element style="border-bottom: 0px;">
                        {{ trans('account/general.api_token_expiration_time') }}
                        <strong>{{ config('passport.expiration_years') }} {{ trans('general.years') }}</strong>.
                    </x-info-element>
                </ul>


            </x-box>

        </x-page-column>
    </x-container>

@stop

@section('moar_scripts')
@endsection
