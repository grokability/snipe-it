{{-- See snipeit_modals.js for what powers this. The password-generator
     wiring and the first-input focus that used to be inline <script>
     blocks on this partial now live inside the modal-load callback in
     snipeit_modals.js so all modal-lifecycle JS is in one place. --}}

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h2 class="modal-title">{{ trans('admin/users/table.createuser') }}</h2>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" action="{{ route('api.users.store') }}" onsubmit="return false">
                <x-alert type="danger" id="modal_error_msg" style="display:none"></x-alert>

                @if ($user->companies->isNotEmpty())
                    <input type="hidden" name="company_id" value="{{ $user->companies->first()->id }}">
                @endif

                <div class="dynamic-form-row">
                    <label for="modal-company_id" class="col-md-3 control-label">{{ trans('general.company') }}:</label>
                    <div class="col-md-9 col-xs-12">
                        <select class="js-data-ajax" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}" name="company_id" id="modal-company_id" style="width:100%">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="dynamic-form-row">
                    <label for="modal-location_id" class="col-md-3 control-label">{{ trans('general.location') }}
                        :</label>
                    <div class="col-md-9 col-xs-12">
                        <select class="js-data-ajax" data-endpoint="locations" data-placeholder="{{ trans('general.select_location') }}" name="location_id" id="modal-location_id" style="width:100%">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="dynamic-form-row">
                    <label for="modal-first_name" class="col-md-3 control-label">{{ trans('general.first_name') }}:
                    </label>
                    <div class="col-md-9 col-xs-12"><input type="text" name="first_name" id="modal-first_name" class="form-control" maxlength="191" required></div>
                </div>

                <div class="dynamic-form-row">
                    <label for="modal-last_name" class="col-md-3 col-xs-12 control-label">{{ trans('general.last_name') }}:</label>
                    <div class="col-md-9 col-xs-12"><input type="text" name="last_name" id="modal-last_name" class="form-control" maxlength="191" required></div>
                </div>

                <div class="dynamic-form-row">
                    <label for="modal-email" class="col-md-3 control-label">{{ trans('admin/users/table.email') }}
                        :</label>

                    <div class="col-md-9 col-xs-12">
                        <input type="email" name="email" id="modal-email" class="form-control" maxlength="191">
                    </div>
                </div>

                <div class="dynamic-form-row">
                    <label for="modal-username" class="col-md-3 col-xs-12 control-label">{{ trans('admin/users/table.username') }}:</label>
                    <div class="col-md-9 col-xs-12"><input type="text" name="username" id="modal-username" class="form-control" maxlength="191" required></div>
                </div>

                {{-- Activated checkbox is rendered ABOVE the password fields
                     so the toggle sits above the inputs whose visibility it
                     controls (see snipeit.js). Defaults to unchecked because
                     the modal is typically used to create a user on-the-fly
                     for asset assignment, where login is not usually needed. --}}
                <div class="dynamic-form-row">
                    <div class="col-md-offset-3 col-md-9">
                        <label class="form-control">
                            <input type="checkbox" value="1" name="activated" id="modal-activated" aria-label="activated">
                            {{ trans('general.login_enabled') }}
                        </label>
                        <x-form.help name="modal-activated" icon="tip">
                            {{ trans('admin/users/general.activated_password_required_help') }}
                        </x-form.help>
                    </div>
                </div>

                <div class="dynamic-form-row" style="display: none;">
                    <label for="modal-password" class="col-md-3 control-label">
                        {{ trans('admin/users/table.password') }}:
                    </label>
                    <div class="col-md-8 col-xs-12">
                        <div class="input-group">
                            <input type="password" name="password" id="modal-password" class="form-control" required>
                            <span class="input-group-addon">
                                <i data-toggle="#modal-password" class="fa fa-fw fa-eye toggle-password" aria-hidden="true"></i>
                                <span class="sr-only">{{ trans('general.toggle_password_visibility') }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <a href="#" class="btn btn-theme btn-sm" id="modal-genPassword" data-tooltip="true" title="{{ trans('admin/users/general.generate_password') }}">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </a>
                    </div>
                </div>

                <div class="dynamic-form-row" style="display: none;">
                    <label for="modal-password_confirmation" class="col-md-3 control-label">
                        {{ trans('admin/users/table.password_confirm') }}:
                    </label>
                    <div class="col-md-8 col-xs-12">
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="modal-password_confirmation" class="form-control" required>
                            <span class="input-group-addon">
                                <i data-toggle="#modal-password_confirmation" class="fa fa-fw fa-eye toggle-password" aria-hidden="true"></i>
                                <span class="sr-only">{{ trans('general.toggle_password_visibility') }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="dynamic-form-row">
                    <label for="modal-display_name" class="col-md-3 control-label">
                        {{ trans('admin/users/table.display_name') }}:
                    </label>
                    <div class="col-md-9 col-xs-12">
                        <input type="text" name="display_name" id="modal-display_name" class="form-control" maxlength="191">
                    </div>
                </div>

            </form>
        </div>
        @include('modals.partials.footer')
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
