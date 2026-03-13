<div>
    <div class="box box-default">
        <div class="box-header with-border">
            <div class="text-right">
                <a class="btn btn-info btn-sm pull-right"
                   onclick="$('#modal-create-token').modal('show');"
                   wire:click="$dispatch('openModal')">
                    {{ trans('general.create') }}
                </a>
            </div>
        </div>
        <div class="box-body">
            <!-- No Tokens Notice -->
            @if($tokens->count() === 0)
                <p>
                    {{ trans('account/general.no_tokens') }}
                </p>
            @endif

            <!-- Personal Access Tokens -->
            <div class="table table-responsive">
            <table class="table table-striped snipe-table">
                @if($tokens->count() > 0)
                    <thead>
                    <tr>
                        <th class="col-md-3">{{ trans('general.name') }}</th>
                        <th class="col-md-2">{{ trans('general.created_at') }}</th>
                        <th class="col-md-2">{{ trans('general.expires') }}</th>
                        <th class="col-md-2"><span class="sr-only">{{ trans('general.delete') }}</span></th>
                    </tr>
                    </thead>
                    <tbody>
                @endif
                @foreach($tokens as $token)

                    <tr>
                        <td>
                            {{ $token->name }}
                        </td>

                        <td>
                            {{ $token->created_at }}
                        </td>

                        <td>
                            {{ $token->expires_at }}
                        </td>
                        <td class="text-right">
                            <a class="action-link btn btn-danger btn-sm" wire:click="deleteToken('{{ $token->id }}')"
                               wire:loading.attr="disabled" data-tooltip="true" title="{{ trans('general.delete') }}">
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
    <!-- Create Token Modal -->
    <div wire:ignore.self class="modal fade" id="modal-create-token" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                    <h4 class="modal-title">
                        Create Token
                    </h4>
                </div>

                <div class="modal-body">
                    <!-- Form Errors -->
                    @if($errors->has('name'))
                        <div class="alert alert-danger">
                            <p><strong>{{ trans('general.whoops') }}</strong> {{ trans('general.something_went_wrong') }}</p>
                            <br>
                            <ul>
                                <li>
                                    @error('name')
                                    <span class="error">
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </li>
                            </ul>
                        </div>
                    @endif

                    <!-- Create Token Form -->
                    <form class="form-horizontal" role="form">
                        <!-- Name -->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="name">Name</label>

                            <div class="col-md-6">
                                <input id="create-token-name" type="text" aria-label="name" class="form-control"
                                       name="name"
                                       wire:keydown.enter="createToken(name)"
                                       wire:model="name"
                                       autofocus
                                       required
                                >
                            </div>
                        </div>
                    </form>

                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn primary" data-dismiss="modal">{{ trans('general.close') }}</button>

                    <button type="button" class="btn btn-primary" wire:click="createToken(name)">
                        {{ trans('general.create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View New Token Modal -->
    <div class="modal fade" id="modal-access-token" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                    <h4 class="modal-title">
                        {{ trans('account/general.personal_access_token') }}
                    </h4>
                </div>

                <div class="modal-body">
                    <p>
                        {{ trans('account/general.here_is_api_key') }}
                    </p>

                    <pre><code>{{ $newTokenString }}</code></pre>
                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('tokenCreated', token => {
            $('#modal-create-token').modal('hide');
            $('#modal-access-token').modal('show');
        })
        window.addEventListener('autoFocusModal', function() {
            $('#modal-create-token').on('shown.bs.modal', function() {
                $(this).find('[autofocus]').focus();
            });
        })
        // was trying to do a submit on the form when enter was pressed
        window.addEventListener("keydown", function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        })
    </script>
</div>