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

            @if ($consumable->auto_consume_on_checkout)
                <x-callout type="info" icon="info">
                    {{ trans('admin/consumables/general.auto_consume_checkout_notice') }}
                </x-callout>
            @endif

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
                name="assigned_user"
                :selected="old('assigned_user', $checkoutRequest?->user_id)"
                :companyId="$consumable->company_id"
                required
            />

            <x-checkout.checkout-notification-callout :item="$consumable" :category="$consumable->category" />

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
        <x-checkout-request-context :request="$checkoutRequest ?? null" :requestable="$consumable" />

        <livewire:checkout-target-panel type="consumables" />
    </x-page-column>

</x-container>

@stop
