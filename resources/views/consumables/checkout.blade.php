@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/consumables/general.checkout') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<x-container columns="2">
    <x-page-column class="col-md-7">

        <x-form route="{{ url()->current() }}" id="checkout_form">

            <x-box header="{{ $consumable->name }}">

            @if ($consumable->name)
                <x-form.static :label="trans('admin/consumables/general.consumable_name')">{{ $consumable->name }}</x-form.static>
            @endif

            @if ($consumable->company)
                <x-form.static :label="trans('general.company')">{!! $consumable->company->present()->formattedNameLink !!}</x-form.static>
            @endif

            @if ($consumable->category)
                <x-form.static :label="trans('general.category')">{!! $consumable->category->present()->formattedNameLink !!}</x-form.static>
            @endif

            <x-form.static :label="trans('admin/components/general.total')">{{ $consumable->qty }}</x-form.static>

            <x-form.static :label="trans('admin/components/general.remaining')">{{ $consumable->numRemaining() }}</x-form.static>

            <x-input.user-select
                :label="trans('general.select_user')"
                name="assigned_to"
                :selected="old('assigned_to')"
                :companyId="$consumable->company_id"
                required
            />

            @if ($consumable->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1' || $consumable->getEula() || ($snipeSettings->webhook_endpoint != ''))
                <div class="form-group notification-callout">
                    <div class="col-md-8 col-md-offset-3">
                        <x-callout type="info" role="status">
                            @if ($consumable->category->require_acceptance == '1')
                                <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_acceptance') }}<br>
                            @endif
                            @if ((string) $snipeSettings->require_accept_signature === '1')
                                <x-icon type="signature" class="fa-fw"/>
                                {{ trans('admin/categories/general.required_signature') }}<br>
                            @endif
                            @if ($consumable->getEula())
                                <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_eula') }}<br>
                            @endif
                            @if ($consumable->category && $consumable->category->checkin_email)
                                <i class="far fa-envelope fa-fw" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.checkin_email_notification') }}<br>
                            @endif
                            @if ($snipeSettings->webhook_endpoint != '')
                                <i class="fab fa-slack fa-fw" aria-hidden="true"></i>
                                {{ trans('general.webhook_msg_note') }}
                            @endif
                        </x-callout>
                    </div>

                    @if ($consumable->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1')
                        <div id="sign_in_place_div" class="col-md-7 col-md-offset-3">
                            <label class="form-control">
                                <input type="checkbox" value="1" name="sign_in_place" @checked(old('sign_in_place', session('sign_in_place', false))) aria-label="{{ trans('general.sign_in_place') }}">
                                {{ trans('general.sign_in_place') }}
                            </label>
                            <p class="help-block">{{ trans('general.sign_in_place_help') }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Checkout quantity -->
            <div class="form-group {{ $errors->has('qty') ? 'has-error' : '' }}">
                <label for="checkout_qty" class="col-md-3 control-label">{{ trans('general.qty') }}</label>
                <div class="col-md-7 col-sm-12">
                    <div class="col-md-2" style="padding-left: 0">
                        <input class="form-control" type="number" name="checkout_qty" id="checkout_qty" value="{{ old('checkout_qty', 1) }}" min="1" max="{{ $consumable->numRemaining() }}" aria-label="{{ trans('general.qty') }}" />
                    </div>
                </div>
                <div class="col-md-8 col-md-offset-3"><x-form.error name="qty" /></div>
            </div>

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$consumable"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="consumables.index"
                    :button_label="trans('general.checkout')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.consumables')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.consumable')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                    ]"
                />
            </x-slot:customfooter>

            </x-box>

        </x-form>

    </x-page-column>

    <x-page-column class="col-md-5">
        <livewire:checkout-target-panel type="consumables" />
    </x-page-column>

</x-container>

@stop
