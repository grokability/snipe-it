@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/accessories/general.bulk_checkout') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container columns="2">
        <x-page-column class="col-md-7">

            <x-form id="bulk_checkout_form" route="{{ route('accessories.bulkcheckout.store') }}" data-disable-empty-on-submit data-autofocus-select2-search>

                <x-box header="{{ trans('admin/accessories/general.bulk_checkout') }}">

                    <x-form.static :label="trans('admin/accessories/general.accessories_selected')">
                        <ul class="list-unstyled" style="margin-bottom: 0;">
                            @foreach ($accessories as $accessory)
                                <li>
                                    <a href="{{ route('accessories.show', $accessory->id) }}">{{ $accessory->name }}</a>
                                    <small class="text-muted">
                                        ({{ trans('admin/components/general.remaining') }}: {{ $accessory->numRemaining() }})
                                    </small>
                                    <input type="hidden" name="ids[]" value="{{ $accessory->id }}">
                                </li>
                            @endforeach
                        </ul>
                    </x-form.static>

                    @if ($excluded->isNotEmpty())
                        <x-callout type="warning" icon="warning">
                            {{ trans('admin/accessories/message.bulk.checkout.excluded_notice') }}
                            <ul>
                                @foreach ($excluded as $accessory)
                                    <li>{{ $accessory->name }}</li>
                                @endforeach
                            </ul>
                        </x-callout>
                    @endif

                    @include('partials.forms.checkout-selector', ['user_select' => 'true', 'asset_select' => 'true', 'location_select' => 'true'])
                    <x-input.user-select
                        :label="trans('general.user')"
                        name="assigned_user"
                        :selected="old('assigned_user')"
                        :style="(session('checkout_to_type') ?: 'user') == 'user' ? null : 'display: none;'"
                    />
                    @include('partials.forms.edit.asset-select', [
                        'translated_name' => trans('general.asset'),
                        'asset_selector_div_id' => 'assigned_asset',
                        'fieldname' => 'assigned_asset',
                        'unselect' => 'true',
                        'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;',
                    ])
                    @include('partials.forms.edit.location-select', [
                        'translated_name' => trans('general.location'),
                        'fieldname' => 'assigned_location',
                        'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;',
                    ])

                    <x-form.row
                        :label="trans('admin/hardware/form.checkout_date')"
                        name="checkout_at"
                        type="datetimepicker"
                        input_div_class="col-md-4"
                    />

                    <x-form.row
                        :label="trans('general.notes')"
                        name="note"
                    >
                        <x-slot:input>
                            <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
                        </x-slot:input>
                    </x-form.row>

                    <x-slot:customfooter>
                        <x-redirect_submit_options
                            index_route="accessories.index"
                            :button_label="trans('general.checkout')"
                            :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.accessories')]),
                                'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                            ]"
                        />
                    </x-slot:customfooter>

                </x-box>

            </x-form>

        </x-page-column>

        <x-page-column class="col-md-5">
            <livewire:checkout-target-panel type="accessories" />
        </x-page-column>

    </x-container>

@stop
