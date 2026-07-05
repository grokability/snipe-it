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

<x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">

    <x-form :$item route="{{ isset($item->id) ? route('accessories.update', ['accessory' => $item->id]) : route('accessories.store') }}">

        <x-box>

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

            <x-input.supplier-select
                :label="trans('general.supplier')"
                name="supplier_id"
                :selected="old('supplier_id', $item->supplier_id)"
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

            <x-form.row
                :label="trans('general.order_number')"
                :$item
                name="order_number"
            />

            <div class="form-group {{ $errors->has('purchase_date') ? 'has-error' : '' }}">
                <label for="purchase_date" class="col-md-3 control-label">{{ trans('general.purchase_date') }}</label>
                <div class="input-group col-md-4">
                    <x-input.datepicker
                        name="purchase_date"
                        id="purchase_date"
                        :value="old('purchase_date', $item->purchase_date ? date('Y-m-d', strtotime($item->purchase_date)) : '')"
                    />
                    {!! $errors->first('purchase_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>

            <x-input.purchase-cost
                :label="trans('general.unit_cost')"
                :item="$item"
                :currencyType="$item->location->currency ?? null"
            />

            <x-input.quantity :item="$item" min="0" />

            <x-input.minimum-quantity :item="$item" />

            <x-form.row
                :label="trans('general.notes')"
                :$item
                name="notes"
                type="textarea"
            />

            @include ('partials.forms.edit.image-upload', ['image_path' => app('accessories_upload_path')])

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
