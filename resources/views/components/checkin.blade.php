@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/components/general.checkin') }}
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">{{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-7">

    <x-form route="{{ route('components.checkin.store', [$component_assets->id, 'backto' => 'asset']) }}">

        <x-box header="{{ $snipe_component->name }}">

            @if ($snipe_component->category)
                <x-form.static :label="trans('general.category')">
                    <x-tag-color :color="$snipe_component->category->tag_color" />
                    {{ $snipe_component->category->name }}
                </x-form.static>
            @endif

            <x-checkin.checked-out-from
                :target="$asset"
                :checkout-date="$checkoutDate"
                :checkout-by="$checkoutBy"
            />

            <x-input.quantity
                name="checkin_qty"
                :value="$component_assets->assigned_qty"
                :min="1"
                :max="$component_assets->assigned_qty"
                :label="trans('general.qty')"
                :help_text="trans('admin/components/general.checkin_limit', ['assigned_qty' => $component_assets->assigned_qty])"
            />

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$snipe_component"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="components.index"
                    :button_label="trans('general.checkin')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.components')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.component')]),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
