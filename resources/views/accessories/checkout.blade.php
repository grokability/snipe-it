@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/accessories/general.checkout') }}
@parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">{{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-9">

    <x-form route="{{ url()->current() }}" id="checkout_form">

        <x-box header="{{ $accessory->name }}">

            @if ($accessory->name)
                <x-form.static :label="trans('admin/accessories/general.accessory_name')">{{ $accessory->name }}</x-form.static>
            @endif

            @if ($accessory->company)
                <x-form.static :label="trans('general.company')">{!! $accessory->company->present()->formattedNameLink !!}</x-form.static>
            @endif

            @if ($accessory->category)
                <x-form.static :label="trans('general.category')">{!! $accessory->category->present()->formattedNameLink !!}</x-form.static>
            @endif

            <x-form.static :label="trans('admin/components/general.total')">{{ $accessory->qty }}</x-form.static>

            <x-form.static :label="trans('admin/components/general.remaining')">{{ $accessory->numRemaining() }}</x-form.static>

            @include ('partials.forms.checkout-selector', ['user_select' => 'true', 'asset_select' => 'true', 'location_select' => 'true'])
            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'company_id' => $accessory->company_id, 'fieldname' => 'assigned_user', 'style' => (session('checkout_to_type') ?: 'user') == 'user' ? '' : 'display: none;'])
            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'asset_selector_div_id' => 'assigned_asset', 'company_id' => $accessory->company_id, 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'company_id' => $accessory->company_id, 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])

            <!-- Checkout quantity -->
            <div class="form-group {{ $errors->has('checkout_qty') ? 'error' : '' }}">
                <label for="checkout_qty" class="col-md-3 control-label">{{ trans('general.qty') }}</label>
                <div class="col-md-7 col-sm-12 required">
                    <div class="col-md-2" style="padding-left: 0">
                        <input class="form-control" type="number" name="checkout_qty" id="checkout_qty" value="{{ old('checkout_qty', 1) }}" min="1" max="{{ $accessory->numRemaining() }}" aria-label="{{ trans('general.qty') }}" />
                    </div>
                </div>
                {!! $errors->first('checkout_qty', '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
            </div>

            @if ($accessory->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1' || $accessory->getEula() || ($snipeSettings->webhook_endpoint != ''))
                <div class="form-group notification-callout">
                    <div class="col-md-8 col-md-offset-3">
                        <div class="callout callout-info">
                            @if ($accessory->requireAcceptance())
                                <i class="far fa-envelope" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_acceptance') }}<br>
                            @endif
                            @if ($accessory->getEula())
                                <i class="far fa-envelope" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_eula') }}<br>
                            @endif
                            @if ($snipeSettings->webhook_endpoint != '')
                                <i class="fab fa-slack" aria-hidden="true"></i>
                                {{ trans('general.webhook_msg_note') }}
                            @endif
                        </div>
                    </div>

                    @if ($accessory->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1')
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

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$accessory"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="accessories.index"
                    :button_label="trans('general.checkout')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.accessories')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.accessory')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
