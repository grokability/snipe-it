@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/licenses/general.checkout') }}
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}
    </a>
@stop

{{-- Page content --}}
@section('content')

    <x-container columns="2">
        <x-page-column class="col-md-7">

            <x-form id="checkout_form" route="{{ url()->current() }}">

                <x-box header="{{ $license->name }} ({{ trans('admin/licenses/message.seats_available', ['seat_count' => $license->availCount()->count()]) }})">

                    <x-form.static :label="trans('admin/hardware/form.name')">{{ $license->name }}</x-form.static>

                    @if ($license->company)
                        <x-form.static :label="trans('general.company')">{!! $license->company->present()->formattedNameLink !!}</x-form.static>
                    @endif

                    @if ($license->category)
                        <x-form.static :label="trans('general.category')">{!! $license->category->present()->formattedNameLink !!}</x-form.static>
                    @endif

                    @if ($license->serial)
                        @can('viewKeys', $license)
                            <x-form.static :label="trans('admin/licenses/form.license_key')">
                                <x-copy-to-clipboard copy_what="license_key">
                                    <code>{!! nl2br(e($license->serial)) !!}</code>
                                </x-copy-to-clipboard>
                            </x-form.static>
                        @endcan
                    @endif

                    @include ('partials.forms.checkout-selector', ['user_select' => 'true', 'asset_select' => 'true', 'location_select' => 'false'])
                    <x-input.user-select
                        :label="trans('general.user')"
                        name="assigned_to"
                        :selected="old('assigned_to')"
                        :companyId="$license->company_id"
                        :style="(session('checkout_to_type') ?: 'user') == 'user' ? null : 'display: none;'"
                    />
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'asset_id', 'company_id' => $license->company_id, 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])

                    <x-form.row
                        :label="trans('general.checkout_note')"
                        name="notes"
                    >
                        <x-slot:input>
                            <textarea class="form-control" id="notes" name="notes" rows="5">{{ old('note') }}</textarea>
                        </x-slot:input>
                    </x-form.row>

                    @if ($license->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1' || $license->getEula() || ($snipeSettings->webhook_endpoint != ''))
                        <div class="form-group notification-callout">
                            <div class="col-md-8 col-md-offset-3">
                                <x-callout type="info" role="status">

                                    @if ($license->requireAcceptance())
                                        <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                        {{ trans('admin/categories/general.required_acceptance') }}
                                        <br>
                                    @endif

                                    @if ((string) $snipeSettings->require_accept_signature === '1')
                                        <x-icon type="signature" class="fa-fw"/>
                                        {{ trans('admin/categories/general.required_signature') }}
                                        <br>
                                    @endif

                                    @if ($license->getEula())
                                        <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                        {{ trans('admin/categories/general.required_eula') }}
                                        <br>
                                    @endif

                                    @if (($license->category) && ($license->category->checkin_email))
                                        <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
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
                            @if ($license->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1')
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
                            index_route="licenses.index"
                            :button_label="trans('general.checkout')"
                            :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.licenses')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.license')]),
                                'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                            ]"
                        />
                    </x-slot:customfooter>

                </x-box>

            </x-form>

        </x-page-column>

        <x-page-column class="col-md-5">
            <livewire:checkout-target-panel type="licenses" />
        </x-page-column>

    </x-container>
@stop
