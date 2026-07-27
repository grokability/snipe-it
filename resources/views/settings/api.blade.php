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
                            <x-form route="{{ route('settings.oauth.request_filters.save') }}">

                                <input type="hidden" name="block_api_user_agents" value="0" />
                                <x-form.checkbox-row
                                    name="block_api_user_agents"
                                    :label="trans('admin/settings/general.block_api_user_agents_text')"
                                    :checked="$blockApiUserAgents"
                                    :help_text="trans('admin/settings/general.block_api_user_agents_help')"
                                    aria-controls="blocked_api_user_agents"
                                    data-toggle="disable-when-unchecked"
                                    data-disable-target="#blocked_api_user_agents"
                                />

                                <x-form.row
                                    :label="trans('admin/settings/general.blocked_api_user_agents_text')"
                                    name="blocked_api_user_agents"
                                    input_div_class="col-md-5"
                                    :help_text="trans('admin/settings/general.blocked_api_user_agents_help')"
                                >
                                    <x-slot:input>
                                        <textarea
                                            class="form-control"
                                            id="blocked_api_user_agents"
                                            name="blocked_api_user_agents"
                                            rows="10"
                                            aria-describedby="blocked_api_user_agents-help"
                                            @disabled(! $blockApiUserAgents)
                                        >{{ $blockedApiUserAgents }}</textarea>
                                    </x-slot:input>
                                </x-form.row>

                                <input type="hidden" name="block_blank_api_user_agents" value="0" />
                                <x-form.checkbox-row
                                    name="block_blank_api_user_agents"
                                    :label="trans('admin/settings/general.block_blank_api_user_agents_text')"
                                    :checked="$blockBlankApiUserAgents"
                                    :help_text="trans('admin/settings/general.block_blank_api_user_agents_help')"
                                />

                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-3">
                                        <button type="submit" class="btn btn-primary">
                                            <x-icon type="checkmark" /> {{ trans('general.save') }}
                                        </button>
                                    </div>
                                </div>
                            </x-form>
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
        <x-container>
            <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
        </x-container>
    @endif

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['simple_view' => true])
@endsection
