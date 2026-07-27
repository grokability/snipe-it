@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.checkout') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .input-group {
            padding-left: 0px !important;
        }
    </style>

    <x-container columns="2">
        <x-page-column class="col-md-7">

            <x-form id="checkout_form" route="{{ url()->current() }}">

                <x-box header="{{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}">

                    @if ($asset->company)
                        <x-form.static :label="trans('general.company')">{!! $asset->company->present()->formattedNameLink !!}</x-form.static>
                    @endif

                    @if ($asset->model->category)
                        <x-form.static :label="trans('general.category')">{!! $asset->model->category->present()->formattedNameLink !!}</x-form.static>
                    @endif

                    <x-form.static :label="trans('admin/hardware/form.model')">
                        @if (($asset->model) && ($asset->model->name))
                            {{ $asset->model->name }}
                        @else
                            <span class="text-danger text-bold">
                                <x-icon type="warning" />
                                {{ trans('admin/hardware/general.model_invalid') }}
                            </span>
                            {{ trans('admin/hardware/general.model_invalid_fix') }}
                            <a href="{{ route('hardware.edit', $asset->id) }}">
                                <strong>{{ trans('admin/hardware/general.edit') }}</strong>
                            </a>
                        @endif
                    </x-form.static>

                    <x-form.row
                        :label="trans('admin/hardware/form.name')"
                        :$item
                        name="name"
                    />

                    <x-form.row
                        :label="trans('admin/hardware/form.status')"
                        name="status_id"
                    >
                        <x-slot:input>
                            <x-input.select
                                name="status_id"
                                :options="$statusLabel_list"
                                :selected="$asset->status_id"
                                required
                                style="width: 100%;"
                                aria-label="status_id"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <x-form.checkbox-row
                        name="requestable"
                        :label="trans('admin/hardware/general.requestable')"
                        :item="$asset"
                    />

                    @include ('partials.forms.checkout-selector', ['user_select' => 'true', 'asset_select' => 'true', 'location_select' => 'true'])
                    <x-input.user-select
                        :label="trans('general.user')"
                        name="assigned_user"
                        :selected="old('assigned_user')"
                        :companyId="$asset->company_id"
                        :style="(session('checkout_to_type') ?: 'user') == 'user' ? null : 'display: none;'"
                    />
                    <!-- unselect keeps the asset being checked out from being pre-selected in this picker -->
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'assigned_asset', 'company_id' => $asset->company_id, 'unselect' => 'true', 'exclude_id' => $asset->id, 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'company_id' => $asset->company_id, 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])

                    <x-form.row
                        :label="trans('admin/hardware/form.checkout_date')"
                        name="checkout_at"
                        type="datetimepicker"
                        :item="$item"
                        :default="date('Y-m-d H:i:s')"
                        input_div_class="col-md-4"
                    />

                    <x-form.row
                        :label="trans('admin/hardware/form.expected_checkin')"
                        name="expected_checkin"
                        type="datetimepicker"
                        :item="$item"
                        :default_now="false"
                        input_div_class="col-md-4"
                    />

                    <x-form.row
                        :label="trans('general.notes')"
                        name="note"
                    >
                        <x-slot:input>
                            <textarea class="col-md-6 form-control" id="note" name="note" @required($snipeSettings->require_checkinout_notes)>{{ old('note', $asset->note) }}</textarea>
                        </x-slot:input>
                    </x-form.row>

                    <!-- Custom fields -->
                    @include('models/custom_fields_form', [
                        'model' => $asset->model,
                        'show_custom_fields_type' => 'checkout',
                    ])

                    @if ($asset->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1' || $asset->getEula() || ($snipeSettings->webhook_endpoint != ''))
                        <div class="form-group notification-callout" style="display:none;">
                            <div class="col-md-8 col-md-offset-3">
                                <x-callout type="info" role="status">

                                    @if ($asset->requireAcceptance())
                                        <x-icon type="email" class="fa-fw"/>
                                        {{ trans('admin/categories/general.required_acceptance') }}
                                        <br>
                                    @endif

                                    @if ((string) $snipeSettings->require_accept_signature === '1')
                                            <x-icon type="signature" class="fa-fw"/>
                                        {{ trans('admin/categories/general.required_signature') }}
                                        <br>
                                    @endif

                                    @if ($asset->getEula())
                                        <x-icon type="email" class="fa-fw"/>
                                        {{ trans('admin/categories/general.required_eula') }}
                                        <br>
                                    @endif

                                    @if (($asset->model?->category) && ($asset->model->category->checkin_email))
                                        <x-icon type="email" class="fa-fw"/>
                                        {{ trans('admin/categories/general.checkin_email_notification') }}
                                        <br>
                                    @endif

                                    @if ($snipeSettings->webhook_endpoint != '')
                                        <i class="fab fa-slack fa-fw" aria-hidden="true"></i>
                                        {{ trans('general.webhook_msg_note') }}
                                    @endif
                                </x-callout>
                            </div>

                            <!-- Sign in place checkbox -->
                            @if ($asset->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1')
                                <div id="sign_in_place_div" class="col-md-7 col-md-offset-3">
                                    <label class="form-control">
                                        <input type="checkbox" value="1" name="sign_in_place" @checked(old('sign_in_place', session('sign_in_place', false))) aria-label="sign_in_place">
                                        {{ trans('general.sign_in_place') }}
                                    </label>
                                    <p class="help-block">
                                        {{ trans('general.sign_in_place_help') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <x-slot:customfooter>
                        <x-redirect_submit_options
                            index_route="hardware.index"
                            :button_label="trans('general.checkout')"
                            :disabled_select="!$asset->model"
                            :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.assets')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                                'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                            ]"
                        />
                    </x-slot:customfooter>

                </x-box>

            </x-form>

        </x-page-column>

        <x-page-column class="col-md-5">
            <livewire:checkout-target-panel type="assets" />
        </x-page-column>

    </x-container>
@stop

@section('moar_scripts')

    <script nonce="{{ csrf_token() }}">
        // Per-user localStorage preference for the requestable default on
        // checkout. Namespaced by user id so a shared browser doesn't leak one
        // user's habit into another user's default. Only takes over when the
        // field wasn't repopulated from a validation-error redirect (old()
        // beats the stored preference). On submit we save whatever the user
        // actually chose, so the preference tracks their real habit.
        const initializeCheckoutRequestablePreference = function () {
            const storageKey = 'snipeit.checkout.requestable_default.' + @json(auth()->id() ?? 'guest');
            const hadOldInput = @json((bool) old('requestable', false)) || @json(session()->has('_old_input.requestable'));
            const checkbox = document.getElementById('requestable');
            const form = checkbox ? checkbox.closest('form') : null;

            if (!checkbox || !form) {
                return;
            }

            if (!hadOldInput) {
                let stored = null;
                try {
                    stored = window.localStorage.getItem(storageKey);
                } catch (e) {
                    // localStorage may be unavailable (private mode, disabled).
                }
                if (stored === '1' || stored === '0') {
                    checkbox.checked = stored === '1';
                }
            }

            form.addEventListener('submit', function () {
                try {
                    window.localStorage.setItem(storageKey, checkbox.checked ? '1' : '0');
                } catch (e) {
                    // Non-fatal: preference just won't persist this time.
                }
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCheckoutRequestablePreference);
        } else {
            initializeCheckoutRequestablePreference();
        }
    </script>
@stop
