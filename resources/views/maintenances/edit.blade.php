@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/maintenances/form.update') }}
    @else
        {{ trans('admin/maintenances/form.create') }}
    @endif
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <x-form :$item route="{{ $item->id ? route('maintenances.update', $item->id) : route('maintenances.store') }}">

            <x-box top_submit>
                @if ($item->id)
                    <x-slot:header>{{ $item->title }}</x-slot:header>
                @endif

                <x-form.row
                    :label="trans('general.name')"
                    :$item
                    name="name"
                    required
                />

                @if (! $item->id)
                    @include ('partials.forms.edit.asset-select', [
                        'translated_name' => trans('general.assets'),
                        'fieldname' => 'selected_assets[]',
                        'multiple' => true,
                        'required' => true,
                        'select_id' => 'assigned_assets_select',
                        'asset_selector_div_id' => 'assets_for_maintenance_div',
                        'asset_ids' => $item->id ? $item->asset()->pluck('id')->toArray() : old('selected_assets'),
                        'asset_id' => $item->id ? $item->asset()->pluck('id')->toArray() : null,
                    ])
                @else
                    @if ($item->asset->company)
                        <x-form.static :label="trans('general.company')">{{ $item->asset->company->name }}</x-form.static>
                    @endif

                    <x-form.static :label="trans('general.asset')">
                        {{ $item->asset ? $item->asset->present()->fullName : '' }}
                    </x-form.static>

                    @if ($item->asset->location)
                        <x-form.static :label="trans('general.location')">{{ $item->asset->location->name }}</x-form.static>
                    @endif
                @endif

                @include ('partials.forms.edit.maintenance_type')

                <x-form.row
                    :label="trans('admin/maintenances/form.responsible_party')"
                    name="responsible_party_id"
                >
                    <x-slot:input>
                        <select
                            class="js-data-ajax select2"
                            data-endpoint="users"
                            name="responsible_party_id"
                            id="responsible_party_id"
                            data-placeholder="{{ trans('general.select_user') }}"
                            aria-label="responsible_party_id"
                            style="width: 100%;"
                        >
                            @if ($item->responsibleParty)
                                <option value="{{ $item->responsibleParty->id }}" selected="selected">
                                    {{ $item->responsibleParty->display_name }}
                                </option>
                            @elseif (! $item->id)
                                <option value="{{ auth()->id() }}" selected="selected">
                                    {{ auth()->user()->display_name }}
                                </option>
                            @endif
                        </select>
                    </x-slot:input>
                </x-form.row>

                <x-form.row
                    :label="trans('admin/maintenances/form.start_date')"
                    name="start_date"
                    type="datetimepicker"
                    :item="$item"
                    input_div_class="col-md-4"
                />

                <x-form.row
                    :label="trans('admin/maintenances/form.completion_date')"
                    name="expected_completion_date"
                    type="datetimepicker"
                    :item="$item"
                    input_div_class="col-md-4"
                />

                {{-- Editable actual-completion date. Available on create as
                     well as edit so users can backfill historical
                     maintenances that were already done before being
                     recorded. Setting this from null → date is equivalent
                     to clicking Mark Complete (fires MaintenanceComplete,
                     stamps completed_by, computes asset_maintenance_time).
                     Clearing it un-completes the row.

                     default_now MUST stay false — the field must never
                     prefill with today. Editing a maintenance to change
                     something unrelated should not accidentally stamp it
                     complete just because the datetimepicker helpfully
                     defaulted the empty value to now. --}}
                <x-form.row
                    :label="trans('admin/maintenances/form.completed_at')"
                    name="completed_at"
                    type="datetimepicker"
                    :item="$item"
                    input_div_class="col-md-4"
                    :help_text="trans('admin/maintenances/form.completed_at_help')"
                    :default_now="false"
                />

                <x-input.supplier-select
                    :label="trans('general.supplier')"
                    name="supplier_id"
                    :selected="old('supplier_id', $item->supplier_id)"
                />

                <x-form.checkbox-row
                    name="is_warranty"
                    :label="trans('admin/maintenances/form.is_warranty')"
                    :item="$item"
                />

                <x-input.purchase-cost
                    name="cost"
                    :label="trans('admin/maintenances/form.cost')"
                    :item="$item"
                    :currencyType="$item->asset?->location?->currency ?: null"
                />

                <x-form.row
                    :label="trans('general.url')"
                    :$item
                    name="url"
                    type="url"
                    input_icon="link"
                    input_group_addon="left"
                    placeholder="https://example.com"
                />

                <x-input.image-upload :item="$item" :imagePath="app('maintenances_path')"/>
                <x-input.file-upload inputId="maintenanceFileUpload"/>

                <x-form.row
                    :label="trans('admin/maintenances/form.notes')"
                    :$item
                    name="notes"
                    type="textarea"
                />

            </x-box>

        </x-form>

    </x-container>

@stop
