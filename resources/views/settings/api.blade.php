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
                            name="api-request-filters"
                            :label="trans('admin/settings/general.api_request_filters')"
                        />

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
                        <x-tabs.pane name="api-request-filters">
                            <form class="form-horizontal" method="post" action="{{ route('settings.oauth.request_filters.save') }}" autocomplete="off">
                                {{ csrf_field() }}

                                <!-- Master: block by User-Agent -->
                                <div class="form-group {{ $errors->has('block_api_user_agents') ? 'error' : '' }}">
                                    <div class="col-md-8 col-md-offset-3">
                                        <label class="form-control">
                                            <input type="hidden" name="block_api_user_agents" value="0"/>
                                            <input type="checkbox" value="1" name="block_api_user_agents" id="block_api_user_agents" aria-controls="blocked_api_user_agents" @checked($blockApiUserAgents) />
                                            <label for="block_api_user_agents">{{ trans('admin/settings/general.block_api_user_agents_text') }}</label>
                                        </label>
                                        <p class="help-block">
                                            {{ trans('admin/settings/general.block_api_user_agents_help') }}
                                        </p>
                                        {!! $errors->first('block_api_user_agents', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    </div>
                                </div>

                                <!-- Blocked patterns textarea (enabled only when master is on) -->
                                <div class="form-group {{ $errors->has('blocked_api_user_agents') ? 'error' : '' }}">
                                    <label for="blocked_api_user_agents" class="col-md-3 control-label">{{ trans('admin/settings/general.blocked_api_user_agents_text') }}</label>
                                    <div class="col-md-5">
                                        <textarea
                                            class="form-control"
                                            id="blocked_api_user_agents"
                                            name="blocked_api_user_agents"
                                            rows="10"
                                            aria-describedby="blocked_api_user_agents_help"
                                            @disabled(! $blockApiUserAgents)
                                        >{{ $blockedApiUserAgents }}</textarea>
                                        {!! $errors->first('blocked_api_user_agents', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}

                                    </div>
                                    <div class="col-md-offset-3 col-md-8">
                                        <p id="blocked_api_user_agents_help" class="help-block">
                                            {{ trans('admin/settings/general.blocked_api_user_agents_help') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Reject blank User-Agents (SCIM routes ignore this) -->
                                <div class="form-group {{ $errors->has('block_blank_api_user_agents') ? 'error' : '' }}">
                                    <div class="col-md-8 col-md-offset-3">
                                        <label class="form-control">
                                            <input type="hidden" name="block_blank_api_user_agents" value="0"/>
                                            <input type="checkbox" value="1" name="block_blank_api_user_agents" id="block_blank_api_user_agents" aria-label="block_blank_api_user_agents" @checked($blockBlankApiUserAgents) />
                                            <label for="block_blank_api_user_agents">{{ trans('admin/settings/general.block_blank_api_user_agents_text') }}</label>
                                        </label>
                                        <p class="help-block">
                                            {!! trans('admin/settings/general.block_blank_api_user_agents_help') !!}
                                        </p>
                                        {!! $errors->first('block_blank_api_user_agents', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-3">
                                        <button type="submit" class="btn btn-primary">
                                            <x-icon type="checkmark"/> {{ trans('general.save') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </x-tabs.pane>

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

    <script nonce="{{ csrf_token() }}">
        (function () {
            var master = document.getElementById('block_api_user_agents');
            var textarea = document.getElementById('blocked_api_user_agents');

            if (!master || !textarea) {
                return;
            }

            var sync = function () {
                textarea.disabled = !master.checked;
            };

            master.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
