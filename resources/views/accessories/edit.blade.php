@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/accessories/general.update') }}
    @else
        {{ trans('admin/accessories/general.create') }}
    @endif
@parent
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

    <x-form :$item route="{{ isset($item->id) ? route('accessories.update', ['accessory' => $item->id]) : route('accessories.store') }}">

        <x-box top_submit>
            @if ($item->id)
                <x-slot:header>{{ $item->name }}</x-slot:header>
            @endif

            <x-input.company-select
                :label="trans('general.company')"
                name="company_id"
                :selected="old('company_id', $item->company_id)"
            />

            <x-form.row
                :label="trans('admin/accessories/general.accessory_name')"
                :$item
                name="name"
            />

            <x-input.category-select
                :label="trans('general.category')"
                name="category_id"
                :selected="old('category_id', $item->category_id)"
                required
                categoryType="accessory"
            />

            <x-input.manufacturer-select
                :label="trans('general.manufacturer')"
                name="manufacturer_id"
                :selected="old('manufacturer_id', $item->manufacturer_id)"
            />

            <x-input.location-select
                :label="trans('general.location')"
                name="location_id"
                :selected="old('location_id', $item->location_id)"
            />

            <x-form.row
                :label="trans('general.model_no')"
                :$item
                name="model_number"
            />

            {{-- Acquisition metadata is create-only. Post-create qty
                 changes flow through the adjust-quantity modal so each
                 change becomes a QuantityAdjust action_log entry (with
                 its own note / order_number / supplier / date / price)
                 instead of a silent parent-column overwrite. Every
                 later acquisition lives on its own Order + OrderItem. --}}
            @if (! $item->id)
                <x-input.supplier-select
                    :label="trans('general.supplier')"
                    name="supplier_id"
                    :selected="old('supplier_id', $item->supplier_id)"
                />

                <x-form.row
                    :label="trans('general.order_number')"
                    :$item
                    name="order_number"
                />

                <x-form.row
                    :label="trans('general.purchase_date')"
                    name="purchase_date"
                    type="datepicker"
                    :item="$item"
                    input_div_class="col-md-4"
                />

                <x-input.purchase-cost
                    :label="trans('general.unit_cost')"
                    :item="$item"
                    :currencyType="$item->location->currency ?? null"
                />

                <x-form.row
                    :label="trans('general.currency_text')"
                    name="currency"
                    input_div_class="col-md-3"
                    help_class="col-md-7 col-md-offset-3"
                    :help_text="trans('general.currency_prefilled_hint').' '.trans('general.currency_manually_entered_help')"
                >
                    <x-slot:input>
                        <x-input.currency-select
                            name="currency"
                            :selected="old('currency', $item->location->currency ?? $snipeSettings->default_currency)"
                        />
                    </x-slot:input>
                </x-form.row>

                <x-input.quantity :item="$item" min="0" />
            @endif

            <x-input.supplier-select
                :label="trans('general.default_supplier')"
                name="default_supplier_id"
                :selected="old('default_supplier_id', $item->default_supplier_id)"
            />

            <x-input.minimum-quantity :item="$item" />

            <x-form.row
                :label="trans('general.notes')"
                :$item
                name="notes"
                type="textarea"
            />

            <x-input.image-upload :item="$item" :imagePath="app('accessories_upload_path')" :clonedModel="$cloned_model ?? null" />

            <x-form.checkbox-row
                name="requestable"
                :label="trans('admin/accessories/general.requestable')"
                :item="$item"
                data-mutex-with="archived"
            />

                {{-- Suppress help_text when an archived validation error is
                     already surfacing. --}}
                <x-form.checkbox-row
                    name="archived"
                    :label="trans('general.archived')"
                    :help_text="$errors->has('archived') ? null : (($item->exists && $item->checkouts()->count() > 0)
                    ? trans('general.archived_blocked_by_checkouts', ['item_type' => trans('general.accessory')])
                    : (($pendingRequestCount > 0)
                        ? trans_choice('general.archived_cancels_pending_requests', $pendingRequestCount, ['count' => $pendingRequestCount, 'item_type' => trans('general.accessory')])
                        : trans('general.archived_help', ['item_type_plural' => trans('general.accessories')])))"
                    :help_icon="$pendingRequestCount > 0 ? 'warning' : null"
                    :help_class="$pendingRequestCount > 0 ? 'text-warning' : null"
                    :item="$item"
                    :disabled="$item->exists && $item->checkouts()->count() > 0"
                    data-mutex-with="requestable"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="accessories.index"
                    :button_label="trans('general.save')"
                    :options="[
                        'back' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.previous_page')]),
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => 'accessories']),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.accessory')]),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
