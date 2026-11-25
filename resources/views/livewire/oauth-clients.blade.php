<div>
    @if ($authorizationError)
        <x-alert type="danger">
            <p>
                {{ trans('admin/users/message.insufficient_permissions') }}
                <br>
                {{ $authorizationError }}
            </p>
        </x-alert>
    @endif

        {{-- show the secret in the modal --}}
        @if ($this->showOauthClients() && $newClientId !== null)
            <div wire:ignore.self class="modal fade" id="modal-oauth-client-reveal" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('button.close') }}">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">{{ trans('admin/settings/general.oauth_client_created_heading') }}</h4>
                        </div>

                        <div class="modal-body">
                            <p>{{ trans('admin/settings/general.oauth_client_created_help') }}</p>

                            <div class="form-group">
                                <label>{{ trans('admin/settings/general.oauth_client_id') }}</label>
                                <x-copy-to-clipboard copy_what="new-oauth-client-id" class="pull-right"/>
                                <pre class="js-copy-new-oauth-client-id" style="clear: both; white-space: pre-wrap; word-break: break-all;"><code>{{ $newClientId }}</code></pre>
                            </div>

                            @if ($newClientSecret !== null)
                                <div class="form-group">
                                    <label>{{ trans('admin/settings/general.oauth_client_secret') }}</label>
                                    <x-copy-to-clipboard copy_what="new-oauth-client-secret" class="pull-right"/>
                                    <pre class="js-copy-new-oauth-client-secret" style="clear: both; white-space: pre-wrap; word-break: break-all;"><code>{{ $newClientSecret }}</code></pre>
                                    <p class="text-warning" style="margin-top: 6px;">
                                        <x-icon type="warning"/>
                                        {{ trans('admin/settings/general.oauth_client_secret_reveal_once') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('general.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @if ($this->showOauthClients())
        @if($clients->count() === 0)
            <p>{{ trans('admin/settings/general.oauth_no_clients') }}</p>
        @else
                {{-- wire:ignore protects bs-table's runtime-added
                     toolbar + wrappers. Without it, every Livewire
                     action on this component morphs the DOM and strips
                     the toolbar (search box, pagination, custom buttons)
                     because the server HTML only emits the bare table
                     markup. Row updates after create/edit/delete have
                     to happen via bs-table's JS API from a Livewire
                     event (see the window.addEventListener block
                     below), not via Livewire's morph. --}}
                <div wire:ignore>
            <div id="OAuthClientsToolbar" class="pull-left" style="min-width: 280px; padding-top: 10px;"></div>
            <table
                data-cookie-id-table="OAuthClientsTable"
                data-id-table="OAuthClientsTable"
                data-toolbar="#OAuthClientsToolbar"
                data-side-pagination="client"
                data-sort-order="desc"
                data-buttons="oauthButtons"
                data-sort-name="created_at"
                id="OAuthClientsTable"
                class="table table-striped snipe-table"
            >
                <thead>
                    <tr>
                        <th scope="col" data-field="id" data-sortable="true">{{ trans('general.id') }}</th>
                        <th scope="col" data-field="name" data-sortable="true">{{ trans('general.name') }}</th>
                        <th scope="col" data-field="client_type" data-sortable="true">{{ trans('admin/settings/general.oauth_client_type') }}</th>
                        <th scope="col" data-field="revoked" data-sortable="true">{{ trans('general.status') }}</th>
                        <th scope="col" data-field="redirect" data-sortable="true">{{ trans('admin/settings/general.oauth_redirect_url') }}</th>
                        <th scope="col" data-field="associated_token_count" data-sortable="true">{{ trans('admin/settings/general.oauth_associated_token_count') }}</th>
                        <th scope="col" data-field="created_at" data-sortable="true">{{ trans('general.created_at') }}</th>
                        <th scope="col" data-field="updated_at" data-sortable="true">{{ trans('general.updated_at') }}</th>
                        <th scope="col">
                            <span class="sr-only">{{ trans('general.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        @php
                            $isPersonalAccessClient = (int) $client->personal_access_client === 1;
                            $isPasswordGrantClient = (int) $client->password_client === 1;
                            $isRevokedClient = (int) $client->revoked === 1;
                            $clientTypeLabel = $isPersonalAccessClient
                                ? trans('admin/settings/general.oauth_client_type_personal_access')
                                : ($isPasswordGrantClient
                                    ? trans('admin/settings/general.oauth_client_type_password_grant')
                                    : trans('admin/settings/general.oauth_client_type_oauth'));
                        @endphp
                        <tr>
                            <td>{{ $client->id }}</td>
                            <td>{{ $client->name }}</td>
                            <td>
                                <span class="label label-info">{{ $clientTypeLabel }}</span>
                            </td>
                            <td>
                                @if($isRevokedClient)
                                    <span class="label label-danger">
                                        <x-icon type="x"/> {{ trans('admin/settings/general.oauth_token_status_revoked') }}
                                    </span>
                                @else
                                    <span class="label label-success">
                                        <x-icon type="checkmark"/> {{ trans('admin/settings/general.oauth_token_status_active') }}
                                    </span>
                                @endif
                            </td>
                            <td>@if ($client->redirect)<code>{{ $client->redirect }}</code>@endif</td>
                            <td>{{ $client->associated_token_count ?? 0 }}</td>
                            <td>{{ $client->created_at ? Helper::getFormattedDateObject($client->created_at, 'datetime', false) : '' }}</td>
                            <td>
                                @if ($client->created_at != $client->updated_at)
                                    {{ $client->updated_at ? Helper::getFormattedDateObject($client->updated_at, 'datetime', false) : '' }}
                                @endif
                            </td>
                            <td class="text-right" style="white-space: nowrap;">
                                @if($isRevokedClient)
                                    <form method="POST" action="{{ route('settings.oauth.clients.unrevoke', ['client' => $client->id]) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-default text-success" data-tooltip="true" title="{{ trans('admin/settings/general.oauth_unrevoke') }}">
                                            <i class="fa-solid fa-toggle-off"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('settings.oauth.clients.revoke', ['client' => $client->id]) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-default text-danger" data-tooltip="true" title="{{ trans('admin/settings/general.oauth_revoke') }}">
                                            <i class="fa-solid fa-toggle-on"></i>
                                        </button>
                                    </form>
                                @endif
                                <a class="action-link btn btn-sm btn-warning" wire:click="editClient('{{ $client->id }}')" onclick="$('#modal-edit-client').modal('show');" data-tooltip="true" title="{{ trans('general.update') }}">
                                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                </a>
                                <a class="action-link btn btn-sm btn-danger" wire:click="deleteClient('{{ $client->id }}')" data-tooltip="true" title="{{ trans('general.delete') }}">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                </div>
        @endif
    @endif

    @if ($this->showAuthorizedApplications())
        @if($authorizedApplications->count() === 0)
            <p>{{ trans('admin/settings/general.oauth_no_clients') }}</p>
        @else
            <div id="AuthorizedAppsToolbar" class="pull-left" style="min-width: 280px; padding-top: 10px;"></div>
            <table
                data-cookie-id-table="AuthorizedAppsTable"
                data-id-table="AuthorizedAppsTable"
                data-toolbar="#AuthorizedAppsToolbar"
                data-side-pagination="client"
                data-sort-order="desc"
                data-sort-name="created_at"
                id="AuthorizedAppsTable"
                class="table table-striped snipe-table"
            >
                <thead>
                    <tr>
                        <th scope="col" data-field="name" data-sortable="true">{{ trans('general.name') }}</th>
                        <th scope="col" data-field="client_owner" data-sortable="true">{{ trans('general.created_by') }}</th>
                        <th scope="col" data-field="oauth_scopes" data-sortable="true">{{ trans('admin/settings/general.oauth_scopes') }}</th>
                        <th scope="col" data-field="created_at" data-sortable="true">{{ trans('general.created_at') }}</th>
                        <th scope="col" data-field="expires" data-sortable="true">{{ trans('general.expires') }}</th>
                        <th scope="col">
                            <span class="sr-only">{{ trans('general.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($authorizedApplications as $application)
                        <tr>
                            <td>{{ $application->client_name }}</td>
                            <td>
                                @php
                                    $ownerLabel = $application->client_owner_display_name ?: $application->client_owner_username;
                                    $ownerSoftDeleted = $application->client_owner_deleted_at !== null;
                                @endphp
                                @if($application->client_owner_id && $ownerLabel)
                                    <a href="{{ route('users.show', $application->client_owner_id) }}">
                                        @if($ownerSoftDeleted)
                                            <del>{{ $ownerLabel }}</del>
                                        @else
                                            {{ $ownerLabel }}
                                        @endif
                                    </a>
                                @else
                                    {{ trans('general.na') }}
                                @endif
                            </td>
                            <td>
                                @if(!$application->scopes)
                                    <span class="label label-default">{{ trans('admin/settings/general.no_scopes') }}</span>
                                @endif
                            </td>
                            <td>{{ $application->created_at ? Helper::getFormattedDateObject($application->created_at, 'datetime', false) : '' }}</td>
                            <td>{{ $application->expires_at ? Helper::getFormattedDateObject($application->expires_at, 'datetime', false) : '' }}</td>
                            <td>
                                <a class="btn btn-sm btn-danger pull-right" wire:click="deleteAuthorizedApplication('{{ $application->client_id }}')">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                    <span class="sr-only">{{ trans('general.delete') }}</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if ($this->showOauthClients())
    <div class="modal fade" id="modal-create-client" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                    <h2 class="modal-title">
                        {{ trans('admin/settings/general.create_client') }}
                    </h2>
                </div>

                <div class="modal-body">
                    <!-- Create Client Form -->
                    <form role="form">
                        <!-- Name -->
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="create-client-name">{{ trans('general.name') }}</label>
                            <input id="create-client-name"
                                   type="text"
                                   aria-label="create-client-name"
                                   class="form-control"
                                   wire:model="name"
                                   wire:keydown.enter="createClient"
                                   required
                                   autofocus>
                            <span class="help-block">
                               {{ trans('admin/settings/general.oauth_name_help') }}
                            </span>
                            <x-form.error name="name"/>
                        </div>

                        <!-- Redirect URL -->
                        <div class="form-group {{ $errors->has('redirect') ? 'has-error' : '' }}">
                            <label for="redirect">{{ trans('admin/settings/general.oauth_redirect_url') }}</label>
                            <input type="url"
                                   class="form-control"
                                   aria-label="redirect"
                                   name="redirect"
                                   wire:model="redirect"
                                   wire:keydown.enter="createClient"
                                   required
                            >
                            <span class="help-block">
                                {{ trans('admin/settings/general.oauth_callback_url') }}
                            </span>
                            <x-form.error name="redirect"/>
                        </div>

                        {{-- Intent picker. Drives confidential off this
                             value server-side (see createClient) so
                             the surface stays simple, two choices,
                             each one a complete config package. --}}
                        <div class="form-group {{ $errors->has('intent') ? 'has-error' : '' }}">
                            <label>{{ trans('admin/settings/general.oauth_client_intent_label') }}</label>
                            <div class="radio" style="margin-top: 8px; margin-bottom: 8px;">
                                <label style="padding-left: 30px; line-height: 1.5;">
                                    <input type="radio"
                                           wire:model="intent"
                                           value="mcp"
                                           style="margin-left: -24px; margin-right: 24px;">
                                    <strong>{{ trans('admin/settings/general.oauth_client_intent_mcp') }}</strong>
                                    <br>
                                    <span class="text-muted">{{ trans('admin/settings/general.oauth_client_intent_mcp_help') }}</span>
                                </label>
                            </div>
                            <div class="radio" style="margin-top: 0; margin-bottom: 0;">
                                <label style="padding-left: 30px; line-height: 1.5;">
                                    <input type="radio"
                                           wire:model="intent"
                                           value="mobile"
                                           style="margin-left: -24px; margin-right: 24px;">
                                    <strong>{{ trans('admin/settings/general.oauth_client_intent_mobile') }}</strong>
                                    <br>
                                    <span class="text-muted">{{ trans('admin/settings/general.oauth_client_intent_mobile_help') }}</span>
                                </label>
                            </div>
                            <x-form.error name="intent"/>
                        </div>
                    </form>
                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    <button type="button"
                            class="btn btn-primary pull-right"
                            wire:click="createClient"
                    >
                        {{ trans('general.create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-client" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">
                        {{ trans('general.update') }}
                    </h4>
                </div>


                <div class="modal-body">
                    @if ($authorizationError)
                        <x-alert type="danger">{{ $authorizationError }}</x-alert>
                    @endif

                    <!-- Edit Client Form -->
                    <form>
                        <!-- Name -->
                        <div class="form-group {{ $errors->has('newName') ? 'has-error' : '' }}">
                            <label for="edit-client-name">{{ trans('general.name') }}</label>
                            <input
                                id="edit-client-name"
                                type="text"
                                aria-label="edit-client-name"
                                class="form-control"
                                wire:model.live="editName"
                                wire:keydown.enter="updateClient('{{ $editClientId }}')"
                            >
                            <span class="help-block">
                                {{ trans('admin/settings/general.oauth_name_help') }}
                            </span>
                            <x-form.error name="newName"/>
                        </div>

                        <!-- Redirect URL -->
                        <div class="form-group {{ $errors->has('newRedirect') ? 'has-error' : '' }}">
                            <label for="redirect">{{ trans('admin/settings/general.oauth_redirect_url') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                name="redirect"
                                aria-label="redirect"
                                wire:model="editRedirect"
                                wire:keydown.enter="updateClient('{{ $editClientId }}')"
                            >
                            <span class="help-block">
                                {{ trans('admin/settings/general.oauth_callback_url')  }}
                            </span>
                            <x-form.error name="newRedirect"/>
                        </div>
                    </form>
                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>

                    <button
                        class="btn btn-primary pull-right"
                            wire:click="updateClient('{{ $editClientId }}')"
                    >
                        {{ trans('general.update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus the first autofocus input whenever the create
            // modal opens. One listener, attached once, so no handler
            // stacking across repeated opens.
            $('#modal-create-client').on('shown.bs.modal', function () {
                $(this).find('[autofocus]').focus();
            });

            // Reset form state on every dismiss path (×, Cancel,
            // click-outside, Esc all fire hidden.bs.modal). We
            // deliberately do NOT dispatch to Livewire here: that
            // would trigger a POST /livewire/update, re-render the
            // component, and the DOM morph would wipe bs-table's
            // runtime toolbar decorations. The Livewire component's
            // Renderless resetCreateClientForm method still exists
            // for direct-dispatch callers (tests, future
            // wire:click), but the modal-dismiss path handles
            // cleanup client-side only. wire:model is NOT .live so
            // the next createClient call picks up whatever's in the
            // DOM at that point, which is the cleared state.
            $('#modal-create-client').on('hidden.bs.modal', function () {
                var $modal = $(this);
                $modal.find('input[type="text"], input[type="url"]').val('');
                $modal.find('input[name="intent"][value="mcp"]').prop('checked', true);
                $modal.find('.has-error').removeClass('has-error');
                $modal.find('.alert-msg').remove();
            });

            $('#modal-edit-client').on('hidden.bs.modal', function () {
                var $modal = $(this);
                // Only clear validation residue here. Leaving the
                // typed values alone lets Livewire's morph on the
                // next editClient() call re-sync editName /
                // editRedirect without fighting a client-side
                // .val('') that would otherwise strand the inputs
                // empty when the morph decides to preserve what's
                // "in the DOM" via its wire:model directive.
                $modal.find('.has-error').removeClass('has-error');
                $modal.find('.alert-msg').remove();
            });
        });
        // bs-table decorations don't survive Livewire's morph and
        // calling bootstrapTable('destroy').bootstrapTable() on the
        // bare post-morph table does NOT rebuild the toolbar.
        // wire:ignore on the table region prevents morph from
        // touching it at all, but then new / edited / deleted rows
        // don't propagate from Livewire state. We solve that by
        // reloading the page after modal close so the fresh render
        // pulls the new row set. The reveal modal (shown on
        // clientCreated) runs its own wait-for-dismiss path so the
        // admin has time to copy the client secret before reload.
        window.addEventListener('clientCreated', function() {
            $('#modal-create-client').modal('hide');
            // After the create modal finishes animating out, open the
            // reveal modal. When that reveal modal in turn closes,
            // reload the page so the new client row is visible in
            // the bs-table.
            $('#modal-create-client').one('hidden.bs.modal', function () {
                $('#modal-oauth-client-reveal').modal('show');
                $('#modal-oauth-client-reveal').one('hidden.bs.modal', function () {
                    window.location.reload();
                });
            });
        });
        window.addEventListener('editClient', function() {
            $('#modal-edit-client').modal('show');
        });
        window.addEventListener('clientUpdated', function() {
            $('#modal-edit-client').modal('hide');
            $('#modal-edit-client').one('hidden.bs.modal', function () {
                window.location.reload();
            });
        });
        window.addEventListener('clientDeleted', function () {
            // Deletes have no modal, so just reload after a short
            // delay so the user sees the success/error flash before
            // the page refreshes.
            setTimeout(function () {
                window.location.reload();
            }, 250);
        });
        // Clear newClientId / newClientSecret from component state
        // when the admin dismisses the reveal modal. Renderless
        // server-side so it does not trigger another morph of the
        // oauth-clients root. Wrapped in DOMContentLoaded so jQuery
        // is loaded before we touch $(document), otherwise the
        // inline script throws at parse time and every listener
        // below it (including the bs-table reinit path) silently
        // fails to register.
        document.addEventListener('DOMContentLoaded', function () {
            $(document).on('hidden.bs.modal', '#modal-oauth-client-reveal', function () {
                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                    Livewire.dispatch('dismissNewClient');
                }
            });
        });
    </script>
    @endif
</div>
