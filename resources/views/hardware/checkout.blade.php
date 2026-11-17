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
                        data-user-preference-key="snipeit.checkout.requestable_default.{{ auth()->id() ?? 'guest' }}"
                        data-had-old-input="{{ ((bool) old('requestable', false)) || session()->has('_old_input.requestable') ? '1' : '0' }}"
                    />

                    @include ('partials.forms.checkout-selector', ['user_select' => 'true', 'asset_select' => 'true', 'location_select' => 'true'])
                    <x-input.user-select
                        :label="trans('general.user')"
                        name="assigned_user"
                        :selected="old('assigned_user', $checkoutRequest?->user_id)"
                        :companyId="$asset->company_id"
                        :style="(session('checkout_to_type') ?: 'user') == 'user' ? null : 'display: none;'"
                    />
                    <!-- unselect keeps the asset being checked out from being pre-selected in this picker -->
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'assigned_asset', 'company_id' => $asset->company_id, 'unselect' => 'true', 'exclude_id' => $asset->id, 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'company_id' => $asset->company_id, 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])

                        <x-input.location-select
                            :label="trans('general.location')"
                            name="location_id"
                            :selected="old('location_id', $asset->location_id)"
                            :company-id="$asset->company_id"
                        />

                        <x-input.location-select
                            :label="trans('admin/hardware/form.default_location')"
                            name="rtd_location_id"
                            :selected="old('rtd_location_id', $asset->rtd_location_id)"
                            :company-id="$asset->company_id"
                        />

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
                        :default="old('expected_checkin', ($checkoutRequest?->end_date ? $checkoutRequest->end_date->toDateString() : ($item->expected_checkin ?? null)))"
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
                        'fieldset' => $asset->custom_fieldset,
                        'defaultValues' => \App\Models\CustomFieldDefault::valuesFor($asset),
                        'show_custom_fields_type' => 'checkout',
                    ])

                    <x-checkout.checkout-notification-callout :item="$asset" :category="$asset->model?->category" />

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
            <x-checkout-request-context :request="$checkoutRequest ?? null" :requestable="$asset" />

            <livewire:checkout-target-panel type="assets" />
        </x-page-column>

    </x-container>
@stop
