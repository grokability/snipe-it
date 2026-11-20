@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/accessories/general.checkin') }}
@parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">{{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-7">

    <x-form route="{{ url()->current() }}">

        <x-box header="{{ $accessory->name }}">

            @if ($accessory->name)
                <x-form.static :label="trans('admin/hardware/form.name')">{{ $accessory->name }}</x-form.static>
            @endif

            @if ($accessory->category)
                <x-form.static :label="trans('general.category')">
                    <x-tag-color :color="$accessory->category->tag_color" />
                    {{ $accessory->category->name }}
                </x-form.static>
            @endif

            <x-checkin.checked-out-from
                :target="$target ?? null"
                :checkout-date="$checkoutLog?->created_at"
                :checkout-by="$checkoutLog?->adminuser"
            />

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$accessory"
                name="note"
                type="textarea"
            />

            <!-- Checkin date -->
            <div class="form-group {{ $errors->has('checkin_at') ? 'has-error' : '' }}">
                <label for="checkin_at" class="col-md-3 control-label">{{ trans('admin/hardware/form.checkin_date') }}</label>
                <div class="col-md-7">
                    <div class="input-group col-md-5 required" style="padding-left: 0">
                        <x-input.datepicker
                            name="checkin_at"
                            id="checkin_at"
                            :value="old('checkin_at', date('Y-m-d'))"
                            end_date="0d"
                        />
                        <x-form.error name="checkin_at" />
                    </div>
                </div>
            </div>

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="accessories.index"
                    :button_label="trans('general.checkin')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.accessories')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.accessory')]),
                        'target' => $target_option,
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
