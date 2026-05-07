@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.oauth_title') }}
    @parent
@stop


{{-- Page content --}}
@section('content')
    @if (!config('app.lock_passwords'))
        <x-container>
                <x-tabs>
                    <x-slot:tabnav>

                        <x-tabs.nav-item
                            name="personal-access-tokens"
                            :label="trans('admin/settings/general.oauth_personal_access_tokens')"
                            :count="$personalAccessTokenCount ?? 0"
                        />

                        <x-tabs.nav-item
                            name="oauth-clients"
                            :label="trans('admin/settings/general.oauth_clients')"
                        />

                        <x-tabs.nav-item
                            name="authorized-applications"
                            :label="trans('admin/settings/general.oauth_authorized_apps')"
                        />
                    </x-slot:tabnav>

                    <x-slot:tabpanes>
                        <x-tabs.pane name="authorized-applications">
                            <div class="oauth-tab-content">
                                <livewire:oauth-clients section="authorized-applications"/>
                            </div>
                        </x-tabs.pane>

                        <x-tabs.pane name="personal-access-tokens">
                            <livewire:admin-personal-access-tokens/>
                        </x-tabs.pane>

                        <x-tabs.pane name="oauth-clients">
                            <div class="oauth-tab-content">
                                <livewire:oauth-clients section="oauth-clients"/>
                            </div>
                        </x-tabs.pane>
                    </x-slot:tabpanes>
                </x-tabs>
        </x-container>
    @else
        <p class="text-warning"><i class="fas fa-lock" aria-hidden="true"></i> {{ trans('general.feature_disabled') }}</p>
    @endif

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['simple_view' => true])
@endsection
