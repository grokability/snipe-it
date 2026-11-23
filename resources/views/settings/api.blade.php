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
                            :label="trans('admin/settings/mcp.approved_oauth_clients')"
                            :count="$approvedOauthClientCount ?? 0"
                        />

                        <x-tabs.nav-item
                            name="pending-oauth-clients"
                            :label="trans('admin/settings/mcp.pending_oauth_clients')"
                            :count="$pendingMcpClients->count()"
                        />
                    </x-slot:tabnav>

                    <x-slot:tabpanes>
                        <x-tabs.pane name="api-request-filters">
                            <x-form route="{{ route('settings.oauth.request_filters.save') }}">

                                <fieldset name="api-user-agent-filtering">
                                    <x-form.legend>
                                        {{ trans('admin/settings/general.legends.api_user_agent_filtering') }}
                                    </x-form.legend>

                                    <input type="hidden" name="block_api_user_agents" value="0"/>
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

                                    <input type="hidden" name="block_blank_api_user_agents" value="0"/>
                                    <x-form.checkbox-row
                                        name="block_blank_api_user_agents"
                                        :label="trans('admin/settings/general.block_blank_api_user_agents_text')"
                                        :checked="$blockBlankApiUserAgents"
                                        :help_text="trans('admin/settings/general.block_blank_api_user_agents_help')"
                                    />
                                </fieldset>

                                <fieldset name="oauth-trusted-lists">
                                    <x-form.legend>
                                        {{ trans('admin/settings/general.legends.oauth_trusted_lists') }}
                                    </x-form.legend>

                                    <x-form.row
                                        :label="trans('admin/settings/mcp.trusted_schemes_label')"
                                        name="mcp_trusted_custom_schemes"
                                        input_div_class="col-md-5"
                                        help_html="{!! trans('admin/settings/mcp.trusted_schemes_help') !!}"
                                    >
                                        <x-slot:input>
                                            <textarea
                                                class="form-control"
                                                id="mcp_trusted_custom_schemes"
                                                name="mcp_trusted_custom_schemes"
                                                rows="4"
                                            >{{ $mcpTrustedCustomSchemes }}</textarea>
                                        </x-slot:input>
                                    </x-form.row>

                                    <x-form.row
                                        :label="trans('admin/settings/mcp.trusted_domains_label')"
                                        name="mcp_trusted_redirect_domains"
                                        input_div_class="col-md-5"
                                        help_html="{!! trans('admin/settings/mcp.trusted_domains_help') !!}"
                                    >
                                        <x-slot:input>
                                            <textarea
                                                class="form-control"
                                                id="mcp_trusted_redirect_domains"
                                                name="mcp_trusted_redirect_domains"
                                                rows="4"
                                            >{{ $mcpTrustedRedirectDomains }}</textarea>
                                        </x-slot:input>
                                    </x-form.row>
                                </fieldset>

                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-3">
                                        <button type="submit" class="btn btn-primary">
                                            <x-icon type="checkmark" /> {{ trans('general.save') }}
                                        </button>
                                    </div>
                                </div>
                            </x-form>
                        </x-tabs.pane>

                        <x-tabs.pane name="personal-access-tokens">
                            <livewire:admin-personal-access-tokens/>
                        </x-tabs.pane>

                        <x-tabs.pane name="oauth-clients">
                            <div class="oauth-tab-content">
                                <livewire:oauth-clients section="oauth-clients"/>
                            </div>
                        </x-tabs.pane>

                        <x-tabs.pane name="pending-oauth-clients">
                            <p>{!! trans('admin/settings/mcp.settings_intro') !!}</p>
                            @if ($pendingMcpClients->isEmpty())
                                <p class="text-muted">{{ trans('admin/settings/mcp.pending_empty') }}</p>
                            @else
                                <div id="PendingOauthClientsToolbar" class="pull-left" style="min-width: 280px; padding-top: 10px;"></div>
                                <table
                                    data-cookie-id-table="PendingOauthClientsTable"
                                    data-id-table="PendingOauthClientsTable"
                                    data-toolbar="#PendingOauthClientsToolbar"
                                    data-side-pagination="client"
                                    data-sort-order="desc"
                                    data-sort-name="created_at"
                                    id="PendingOauthClientsTable"
                                    class="table table-striped snipe-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="name" data-sortable="true">{{ trans('admin/settings/mcp.pending_col_name') }}</th>
                                            <th scope="col" data-field="redirect" data-sortable="true">{{ trans('admin/settings/mcp.pending_col_redirect') }}</th>
                                            <th scope="col" data-field="registered_ip" data-sortable="true">{{ trans('admin/settings/mcp.pending_col_registered_ip') }}</th>
                                            <th scope="col" data-field="created_at" data-sortable="true">{{ trans('admin/settings/mcp.pending_col_registered_at') }}</th>
                                            <th scope="col">
                                                <span class="sr-only">{{ trans('admin/settings/mcp.pending_col_actions') }}</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingMcpClients as $mcpClient)
                                            <tr>
                                                <td>{{ $mcpClient->name }}</td>
                                                <td><code>{{ $mcpClient->primary_redirect_uri }}</code></td>
                                                <td>{{ $mcpClient->registered_ip ?? '-' }}</td>
                                                <td>{{ $mcpClient->created_at ? Helper::getFormattedDateObject($mcpClient->created_at, 'datetime', false) : '-' }}</td>
                                                <td>
                                                    <form method="POST" action="{{ route('settings.oauth.mcp.clients.approve', ['client' => $mcpClient->id]) }}" style="display:inline-block">
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-success"
                                                            data-tooltip="true"
                                                            title="{{ trans('admin/settings/mcp.pending_action_approve') }}"
                                                            aria-label="{{ trans('admin/settings/mcp.pending_action_approve') }}"
                                                        >
                                                            <x-icon type="checkmark"/>
                                                        </button>
                                                    </form>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger"
                                                        data-toggle="modal"
                                                        data-target="#rejectOauthClientModal"
                                                        data-client-id="{{ $mcpClient->id }}"
                                                        data-client-name="{{ $mcpClient->name }}"
                                                        data-client-redirect="{{ $mcpClient->primary_redirect_uri }}"
                                                        data-tooltip="true"
                                                        title="{{ trans('admin/settings/mcp.pending_action_reject') }}"
                                                        aria-label="{{ trans('admin/settings/mcp.pending_action_reject') }}"
                                                    >
                                                        <x-icon type="x"/>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                {{--  Reject modal --}}
                                <div class="modal fade" id="rejectOauthClientModal" tabindex="-1" role="dialog" aria-labelledby="rejectOauthClientModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST" id="rejectOauthClientForm">
                                                @csrf
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('button.cancel') }}">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    <h4 class="modal-title" id="rejectOauthClientModalLabel">
                                                        {{ trans('admin/settings/mcp.reject_modal_title') }}
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ trans('admin/settings/mcp.reject_modal_intro') }}</p>
                                                    <p class="text-muted">
                                                        <strong>{{ trans('admin/settings/mcp.pending_col_name') }}</strong>
                                                        <span data-reject-client-name></span>
                                                    </p>
                                                    <p class="text-muted">
                                                        <strong>{{ trans('admin/settings/mcp.pending_col_redirect') }}</strong>
                                                        <code data-reject-client-redirect></code>
                                                    </p>
                                                    <div class="form-group">
                                                        <label for="reject_rejected_reason">{{ trans('admin/settings/mcp.reject_modal_reason_label') }}</label>
                                                        <textarea
                                                            class="form-control"
                                                            name="rejected_reason"
                                                            id="reject_rejected_reason"
                                                            rows="3"
                                                            placeholder="{{ trans('admin/settings/mcp.pending_action_reject_reason_placeholder') }}"
                                                        ></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <x-icon type="x"/>
                                                        {{ trans('admin/settings/mcp.pending_action_reject') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
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

    {{-- Reject-OAuth-client modal wiring. When the modal opens, read
         data-client-id / data-client-name / data-client-redirect off
         the triggering button and inject them into the form action +
         context fields so one modal serves every row in the pending
         table. The template URL is built via route() with a
         placeholder client id so Laravel resolves the correct prefix
         (no /admin segment) and the client id gets swapped in at
         modal-open time. --}}
    @php
        $rejectRouteTemplate = route('settings.oauth.mcp.clients.reject', ['client' => '__CLIENT_ID__']);
    @endphp
    <script nonce="{{ csrf_token() }}">
        (function () {
            var rejectRouteTemplate = @json($rejectRouteTemplate);

            $(document).on('show.bs.modal', '#rejectOauthClientModal', function (event) {
                var trigger = $(event.relatedTarget);
                var clientId = trigger.data('client-id');
                var clientName = trigger.data('client-name') || '';
                var clientRedirect = trigger.data('client-redirect') || '';

                var modal = $(this);
                modal.find('[data-reject-client-name]').text(clientName);
                modal.find('[data-reject-client-redirect]').text(clientRedirect);
                modal.find('#rejectOauthClientForm').attr(
                    'action',
                    rejectRouteTemplate.replace('__CLIENT_ID__', encodeURIComponent(clientId)),
                );
                modal.find('#reject_rejected_reason').val('');
            });
        })();
    </script>
@endsection
