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

                    <x-checkout.target-selector :item="$license" />
                    <x-input.user-select
                        :label="trans('general.user')"
                        name="assigned_user"
                        :selected="old('assigned_user', $checkoutRequest?->user_id)"
                        :companyId="$license->company_id"
                        :style="(session('checkout_to_type') ?: 'user') == 'user' ? null : 'display: none;'"
                    />
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'assigned_asset', 'company_id' => $license->company_id, 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])

                    <x-form.row
                        :label="trans('general.checkout_note')"
                        name="notes"
                    >
                        <x-slot:input>
                            <textarea class="form-control" id="notes" name="notes" rows="5">{{ old('note') }}</textarea>
                        </x-slot:input>
                    </x-form.row>

                    <x-checkout.checkout-notification-callout :item="$license" :category="$license->category" />

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
            <x-checkout-request-context :request="$checkoutRequest ?? null" :requestable="$license" />

            <livewire:checkout-target-panel type="licenses" />
        </x-page-column>

    </x-container>
@stop
