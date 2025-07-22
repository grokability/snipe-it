<div>
    <div class="box box-default">

        <div class="box-header">
                <h2 class="box-title">
                    <x-icon type="oauth"/>
                    {{ trans('admin/settings/general.oauth_clients') }}
                </h2>
                @if ($authorizationError)
                    <div class="alert alert-danger">
                        <p>{{ trans('admin/users/message.insufficient_permissions') }}
                        <br>
                        {{ $authorizationError }}
                        </p>
                    </div>
                @endif

                <div class="box-tools pull-right">
                        <a class="btn btn-primary"
                           wire:click="$dispatch('openModal')"
                           onclick="$('#modal-create-client').modal('show');">
                            {{ trans('general.create') }}
                        </a>
                </div>
            </div>

            <div class="box-body">
                <!-- Current Clients -->
                @if($clients->count() === 0)
                    <p>
                        {{ trans('admin/settings/general.oauth_no_clients') }}
                    </p>
                @endif

            @if ($clients->count() > 0)
                    <table data-cookie-id-table="OAuthClientsTable"
                           data-id-table="OAuthClientsTable"
                           data-side-pagination="client"
                           data-sort-order="desc"
                           data-sort-name="created_at"
                           id="OAuthClientsTable"
                           class="table table-striped snipe-table">
                    <thead>
                        <tr>
                            <th>{{ trans('general.id') }}</th>
                            <th data-sortable="true">{{ trans('general.name') }}</th>
                            <th data-sortable="true">{{ trans('admin/settings/general.oauth_redirect_url') }}</th>
                            <th data-sortable="true">{{ trans('admin/settings/general.oauth_secret') }}</th>
                            <th data-sortable="true">{{ trans('general.created_at')  }}</th>
                            <th data-sortable="true">{{ trans('general.updated_at')  }}</th>
                            <th>
                                <span class="sr-only">
                                    {{ trans('general.actions') }}
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <!-- ID -->
                                <td>
                                    {{ $client->id }}
                                </td>

                                <!-- Name -->
                                <td>
                                    {{ $client->name }}
                                </td>

                                <!-- Redirect -->
                                <td>
                                    <code>{{ $client->redirect }}</code>
                                </td>

                                <!-- Secret -->
                                <td>
                                    <code>{{ $client->secret }}</code>
                                </td>

                                <td>
                                    {{ $client->created_at ? Helper::getFormattedDateObject($client->created_at, 'datetime', false) : '' }}
                                </td>

                                <td>
                                    @if ($client->created_at != $client->updated_at)
                                        {{ $client->updated_at ? Helper::getFormattedDateObject($client->updated_at, 'datetime', false) : '' }}
                                    @endif
                                </td>

                                <!-- Edit / Delete Button -->
                                <td class="text-right">

                                    <a class="action-link btn btn-sm btn-warning"
                                       wire:click="editClient('{{ $client->id }}')"
                                       onclick="$('#modal-edit-client').modal('show');">
                                        <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                        <span class="sr-only">
                                            {{ trans('general.update') }}
                                        </span>
                                    </a>

                                    <a class="action-link btn btn-danger btn-sm" wire:click="deleteClient('{{ $client->id }}')">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                        <span class="sr-only">
                                            {{ trans('general.delete') }}
                                        </span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>



        <div>
            @if ($authorized_tokens->count() > 0)
                <div>
                    <div class="box box-default">
                        <div class="box-header">
                            <h2 class="box-title">
                                {{ trans('admin/settings/general.oauth_authorized_apps') }}
                            </h2>
                        </div>

                        <div class="box-body">
                            <!-- Authorized Tokens -->
                            <table data-cookie-id-table="AuthorizedAppsTable"
                                   data-id-table="AuthorizedAppsTable"
                                   data-toolbar="#AuthorizedAppsToolbar"
                                   data-side-pagination="client"
                                   data-sort-order="desc"
                                   data-sort-name="created_at"
                                   id="AuthorizedAppsTable"
                                   class="table table-striped snipe-table">
                                <thead>
                                <tr>
                                    <th data-sortable="true">{{ trans('general.name') }}</th>
                                    <th data-sortable="true"> {{ trans('account/general.personal_access_token') }}</th>
                                    <th data-sortable="true">{{ trans('admin/settings/general.oauth_scopes')  }}</th>
                                    <th data-sortable="true">{{ trans('general.created_at')  }}</th>
                                    <th data-sortable="true">{{ trans('general.expires') }}</th>
                                    <th>
                                        <span class="sr-only">
                                            {{ trans('general.actions') }}
                                        </span>
                                    </th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($authorized_tokens as $token)
                                    <tr>
                                        <!-- Client Name -->
                                        <td>
                                            {{ $token->client->name }}
                                        </td>

                                        <td>
                                            {{ $token->name }}
                                        </td>

                                        <!-- Scopes -->
                                        <td>
                                            @if(!$token->scopes)
                                                <span class="label label-default">
                                                    {{ trans('admin/settings/general.no_scopes') }}
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $token->created_at ? Helper::getFormattedDateObject($token->created_at, 'datetime', false) : '' }}
                                        </td>

                                        <td>
                                            {{ $token->expires_at ? Helper::getFormattedDateObject($token->expires_at, 'datetime', false) : '' }}
                                        </td>
                                        <!-- Revoke Button -->
                                        <td>
                                            <a class="btn btn-sm btn-danger pull-right"
                                                wire:click="deleteToken('{{ $token->id }}')"
                                            >
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                                <span class="sr-only">
                                                    {{ trans('general.delete') }}
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif





    <!-- Create Client Modal -->
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
</div>


    <!-- Edit Client Modal -->
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
</div>

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@endsection


