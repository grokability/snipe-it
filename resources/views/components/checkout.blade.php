@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/components/general.checkout') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-8">

    <x-form route="{{ route('components.checkout.store', $snipe_component->id) }}" id="checkout_form">

        <x-box header="{{ $snipe_component->name }} ({{ $snipe_component->numRemaining() }} {{ trans('admin/components/general.remaining') }})">

            @if ($snipe_component->company)
                <x-form.static :label="trans('general.company')">{!! $snipe_component->company->present()->formattedNameLink !!}</x-form.static>
            @endif

            @if ($snipe_component->category)
                <x-form.static :label="trans('general.category')">{!! $snipe_component->category->present()->formattedNameLink !!}</x-form.static>
            @endif

            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'asset_id', 'company_id' => $snipe_component->company_id, 'required' => 'true', 'value' => old('asset_id')])

            <x-input.quantity
                name="assigned_qty"
                :value="1"
                :min="1"
                :max="$snipe_component->numRemaining()"
                :label="trans('general.qty')"
            />

            @if ($snipe_component->requireAcceptance() || $snipe_component->getEula() || ($snipeSettings->webhook_endpoint != ''))
                <div class="form-group notification-callout">
                    <div class="col-md-8 col-md-offset-3">
                        <div class="callout callout-info">
                            @if ($snipe_component->category->require_acceptance == '1')
                                <i class="far fa-envelope" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_acceptance') }}<br>
                            @endif
                            @if ($snipe_component->getEula())
                                <i class="far fa-envelope" aria-hidden="true"></i>
                                {{ trans('admin/categories/general.required_eula') }}<br>
                            @endif
                            @if ($snipeSettings->webhook_endpoint != '')
                                <i class="fab fa-slack" aria-hidden="true"></i>
                                {{ trans('general.webhook_msg_note') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$snipe_component"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="components.index"
                    :button_label="trans('general.checkout')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.components')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.component')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
