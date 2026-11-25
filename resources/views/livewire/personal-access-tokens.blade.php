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
                        <th scope="col" class="col-md-4">{{ trans('general.name') }}</th>
                        <th scope="col" class="col-md-3">{{ trans('general.created_at') }}</th>
                        <th scope="col" class="col-md-3">{{ trans('general.expires') }}</th>
                        <th scope="col" class="col-md-1"><span class="sr-only">{{ trans('general.delete') }}</span></th>
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
                            {{ $token->created_at ? \App\Helpers\Helper::getFormattedDateObject($token->created_at, 'datetime', false) : '' }}
                        </td>

                        <td>
                            {{ $token->expires_at ? \App\Helpers\Helper::getFormattedDateObject($token->expires_at, 'datetime', false) : '' }}
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
                    <form role="form">
                        <!-- Name -->
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="create-token-name">{{ trans('general.name') }}</label>
                            <input id="create-token-name" type="text" aria-label="name" class="form-control"
                                   name="name"
                                   wire:keydown.enter="createToken"
                                   wire:model="name"
                                   autofocus
                                   required
                            >
                            <x-form.error name="name" />
                        </div>
                    </form>
                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>

                    <button type="button" class="btn btn-primary pull-right" wire:click="createToken">
                        {{ trans('general.create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View New Token Modal -->
    <div wire:ignore.self class="modal fade" id="modal-access-token" tabindex="-1" role="dialog">
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

                    {{-- Copy icon floated right of the row right above
                         the bearer <pre>. clipboard.js data-target
                         points at the pre's .js-copy-pat-bearer class
                         so only the token text is copied, not the
                         icon's sr-only label. --}}
                    <x-copy-to-clipboard copy_what="pat-bearer" class="pull-right" style="margin-bottom: 4px;" />
                    <pre class="js-copy-pat-bearer" style="clear: both; white-space: pre-wrap; word-break: break-all;"><code>{{ $newTokenString }}</code></pre>
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
        // Clear name + validation state every time the create modal
        // closes (×, Cancel, click-outside, Esc all fire
        // hidden.bs.modal). Without this, reopening the modal shows
        // the previous attempt's red has-error outline and error
        // message against a field the user has not touched again.
        // Wrapped in DOMContentLoaded so jQuery is loaded before we
        // touch $(). Without the wrapper the script throws at parse
        // time ("$ is not defined") and every listener below it
        // silently fails to register.
        document.addEventListener('DOMContentLoaded', function () {
            $('#modal-create-token').on('hidden.bs.modal', function () {
                Livewire.dispatch('resetCreateTokenForm');
            });
        });
        // was trying to do a submit on the form when enter was pressed
        window.addEventListener("keydown", function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        })
    </script>
</div>