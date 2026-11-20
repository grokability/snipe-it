@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/licenses/general.checkin') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-7">

    <x-form :route="route('licenses.checkin.save', ['licenseId' => $licenseSeat->id, 'backTo' => $backto])">

        <x-box header="{{ $licenseSeat->license->name }}">

            <x-form.static :label="trans('general.name')">{{ $licenseSeat->license->name }}</x-form.static>

            @if ($licenseSeat->license->company)
                <x-form.static :label="trans('general.company')">
                    {!! $licenseSeat->license->company->present()->formattedNameLink !!}
                </x-form.static>
            @endif

            @if ($licenseSeat->license->category)
                <x-form.static :label="trans('general.category')">
                    <x-tag-color :color="$licenseSeat->license->category->tag_color" />
                    {{ $licenseSeat->license->category->name }}
                </x-form.static>
            @endif

            <x-checkin.checked-out-from
                :target="$licenseSeat->user ?? $licenseSeat->asset"
                :checkout-date="$checkoutLog?->created_at"
                :checkout-by="$checkoutLog?->adminuser"
            />

            @if ($licenseSeat->license->serial)
                @can('viewKeys', $licenseSeat->license)
                    <x-form.static :label="trans('admin/licenses/form.license_key')">
                        <x-copy-to-clipboard copy_what="license_key">
                            <code>{!! nl2br(e($licenseSeat->license->serial)) !!}</code>
                        </x-copy-to-clipboard>
                    </x-form.static>
                @endcan
            @endif

            <x-form.row
                :label="trans('general.checkin_note')"
                name="notes"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="licenses.index"
                    :button_label="trans('general.checkin')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.licenses')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.license')]),
                        'target' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
