<div>
    @if ($authorizationError)
        <div class="alert alert-danger">
            <p>
                {{ trans('admin/users/message.insufficient_permissions') }}
                <br>
                {{ $authorizationError }}
            </p>
        </div>
    @endif

    @if ($this->showOauthClients())
        @if($clients->count() === 0)
            <p>{{ trans('admin/settings/general.oauth_no_clients') }}</p>
        @else
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
                        <th data-field="id" data-sortable="true">{{ trans('general.id') }}</th>
                        <th data-field="name" data-sortable="true">{{ trans('general.name') }}</th>
                        <th data-field="client_type" data-sortable="true">{{ trans('admin/settings/general.oauth_client_type') }}</th>
                        <th data-field="revoked" data-sortable="true">{{ trans('general.status') }}</th>
                        <th data-field="redirect" data-sortable="true">{{ trans('admin/settings/general.oauth_redirect_url') }}</th>
                        <th data-field="secret" data-sortable="true">{{ trans('admin/settings/general.oauth_secret') }}</th>
                        <th data-field="associated_token_count" data-sortable="true">{{ trans('admin/settings/general.oauth_associated_token_count') }}</th>
                        <th data-field="created_at" data-sortable="true">{{ trans('general.created_at') }}</th>
                        <th data-field="updated_at" data-sortable="true">{{ trans('general.updated_at') }}</th>
                        <th>
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
                            <td><code>{{ $client->redirect }}</code></td>
                            <td><code>{{ $client->secret }}</code></td>
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
                        <th data-field="name" data-sortable="true">{{ trans('general.name') }}</th>
                        <th data-field="client_owner" data-sortable="true">{{ trans('general.created_by') }}</th>
                        <th data-field="oauth_scopes" data-sortable="true">{{ trans('admin/settings/general.oauth_scopes') }}</th>
                        <th data-field="created_at" data-sortable="true">{{ trans('general.created_at') }}</th>
                        <th data-field="expires" data-sortable="true">{{ trans('general.expires') }}</th>
                        <th>
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
                    <!-- Form Errors -->
                    @if($errors->has('name') || $errors->has('redirect'))
                        <div class="alert alert-danger">
                            <p><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                @if($errors->has('name'))
                                    <li>{{ $errors->first('name') }}</li>
                                @endif
                                @if($errors->has('redirect'))
                                    <li>{{ $errors->first('redirect') }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <!-- Create Client Form -->
                    <form class="form-horizontal" role="form">
                        <!-- Name -->
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="create-client-name">
                                {{ trans('general.name') }}
                            </label>

                            <div class="col-md-7">
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
                            </div>
                        </div>

                        <!-- Redirect URL -->
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="redirect">{{ trans('admin/settings/general.oauth_redirect_url') }}</label>

                            <div class="col-md-7">
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
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button"
                            class="btn btn-primary"
                            wire:click="createClient"
                    >
                        Create
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
                    @if($errors->has('newName') || $errors->has('newRedirect'))
                        <div class="alert alert-danger">
                            <p><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                @if($errors->has('newName'))
                                    <li>{{ $errors->first('newName') }}</li>
                                @endif
                                @if($errors->has('newRedirect'))
                                    <li>{{ $errors->first('newRedirect') }}</li>
                                @endif
                                @if($authorizationError)
                                    <li>{{ $authorizationError }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <!-- Edit Client Form -->
                    <form class="form-horizontal">
                        <!-- Name -->
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="edit-client-name">Name</label>

                            <div class="col-md-7">
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
                            </div>
                        </div>

                        <!-- Redirect URL -->
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="redirect">{{ trans('admin/settings/general.oauth_redirect_url') }}</label>

                            <div class="col-md-7">
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
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                    <button
                            class="btn btn-primary"
                            wire:click="updateClient('{{ $editClientId }}')"
                    >
                        Update Client
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Livewire.on('openModal', () => {
                $('#modal-create-client').modal('show').on('shown.bs.modal', function() {
                    $(this).find('[autofocus]').focus();
                });
            });
        });
        window.addEventListener('clientCreated', function() {
            $('#modal-create-client').modal('hide');
        });
        window.addEventListener('editClient', function() {
            $('#modal-edit-client').modal('show');
        });
        window.addEventListener('clientUpdated', function() {
            $('#modal-edit-client').modal('hide');
        });
    </script>
    @endif
</div>
