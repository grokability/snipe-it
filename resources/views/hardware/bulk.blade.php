@use('App\Models\Location', 'Location')

@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/form.update') }}
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-sm btn-theme pull-right">
        {{ trans('general.back') }}
    </a>
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-md-8 col-md-offset-2">

        <p>{{ trans('admin/hardware/form.bulk_update_help') }}</p>

        <x-form :route="route('hardware/bulksave')">
            <x-box>

                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans_choice('admin/hardware/form.bulk_update_warn', count($assets), ['asset_count' => count($assets)]) }}

                    @if (count($modelNames) > 1)
                        {{ trans('admin/hardware/form.bulk_update_with_custom_field', ['asset_model_count' => count($modelNames)]) }}
                    @endif
                </x-callout>

                {{-- Name + inline null-toggle. The col-md-5 sibling next
                     to the input doesn't fit any current x-form.row slot
                     (after_input is col-md-1), so this stays hand-rolled.
                     Same pattern for purchase_date / expected_checkin /
                     asset_eol_date / next_audit_date below. --}}
                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                    <label for="name" class="col-md-3 control-label">
                        {{ trans('admin/hardware/form.name') }}
                    </label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" maxlength="191" style="width:100%">
                        <x-form.error name="name" />
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_name"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                </div>

                {{-- Purchase date --}}
                <div class="form-group{{ $errors->has('purchase_date') ? ' has-error' : '' }}">
                    <label for="purchase_date" class="col-md-3 control-label">{{ trans('admin/hardware/form.date') }}</label>
                    <div class="col-md-4">
                        <x-input.datepicker
                            name="purchase_date"
                            value="{{ old('purchase_date') }}"
                            placeholder="{{ trans('general.select_date') }}"
                        />
                        <x-form.error name="purchase_date" />
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_purchase_date"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                </div>

                {{-- Expected checkin date --}}
                <div class="form-group{{ $errors->has('expected_checkin') ? ' has-error' : '' }}">
                    <label for="expected_checkin" class="col-md-3 control-label">{{ trans('admin/hardware/form.expected_checkin') }}</label>
                    <div class="col-md-4">
                        <x-input.datetimepicker
                            name="expected_checkin"
                            value="{{ old('expected_checkin') }}"
                            :default_now="false"
                        />
                        <x-form.error name="expected_checkin" />
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_expected_checkin_date"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                </div>

                {{-- EOL date --}}
                <div class="form-group{{ $errors->has('asset_eol_date') ? ' has-error' : '' }}">
                    <label for="asset_eol_date" class="col-md-3 control-label">{{ trans('admin/hardware/form.eol_date') }}</label>
                    <div class="col-md-4">
                        <x-input.datepicker
                            name="asset_eol_date"
                            value="{{ old('asset_eol_date') }}"
                            placeholder="{{ trans('general.select_date') }}"
                        />
                        <x-form.error name="asset_eol_date" />
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_asset_eol_date"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                </div>

                {{-- Auto-calculate EOL based on purchase date + model EOL rule --}}
                <x-form.checkbox-row
                    name="calc_eol"
                    :label="trans('admin/hardware/form.calc_eol')"
                />

                {{-- Status. Includes a status-compatibility help block and a
                     live status_check() AJAX result span (populated by the
                     JS handler in @section('moar_scripts') below). --}}
                <x-form.row
                    :label="trans('admin/hardware/form.status')"
                    name="status_id"
                    input_div_class="col-md-7"
                >
                    <x-slot:input>
                        <x-input.select
                            name="status_id"
                            :options="$statuslabel_list"
                            :selected="old('status_id')"
                            style="width: 100%"
                            aria-label="status_id"
                        />
                        <p class="help-block">{{ trans('general.status_compatibility') }}</p>
                        <p
                            id="selected_status_status"
                            role="status"
                            aria-live="polite"
                            aria-atomic="true"
                            style="display:none;"
                            data-deployable-label="{{ trans_choice('admin/hardware/form.asset_deployable', 2) }}"
                            data-not-deployable-label="{{ trans_choice('admin/hardware/form.asset_not_deployable_checkin', 2) }}"
                        ></p>
                    </x-slot:input>
                </x-form.row>

                <x-input.model-select
                    :label="trans('admin/hardware/form.model')"
                    name="model_id"
                    :selected="old('model_id')"
                />

                <div class="form-group {{ $errors->has('rtd_location_id') ? ' has-error' : '' }}">
                    <label for="rtd_location_id" class="col-md-3 control-label">
                        {{ trans('admin/hardware/form.default_location') }}
                    </label>
                    <div class="col-md-4">
                        <select
                            class="js-data-ajax"
                            data-endpoint="locations"
                            data-placeholder="{{ trans('general.select_location') }}"
                            name="rtd_location_id"
                            id="rtd_location_id"
                            style="width:100%"
                            aria-label="rtd_location_id"
                        >
                            <option value=""></option>
                            @if (old('rtd_location_id'))
                                <option value="{{ old('rtd_location_id') }}" selected="selected">
                                    {{ Location::find(old('rtd_location_id'))?->name }}
                                </option>
                            @endif
                        </select>
                        <x-form.error name="rtd_location_id"/>
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_rtd_location_id"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                </div>

                {{-- Current location + nullable checkbox. --}}
                <div class="form-group {{ $errors->has('location_id') ? ' has-error' : '' }}">
                    <label for="location_id" class="col-md-3 control-label">
                        {{ trans('general.location') }}
                    </label>
                    <div class="col-md-4">
                        <select
                            class="js-data-ajax"
                            data-endpoint="locations"
                            data-placeholder="{{ trans('general.select_location') }}"
                            name="location_id"
                            id="location_id"
                            style="width:100%"
                            aria-label="location_id"
                        >
                            <option value=""></option>
                            @if (old('location_id'))
                                <option value="{{ old('location_id') }}" selected="selected">
                                    {{ Location::find(old('location_id'))?->name }}
                                </option>
                            @endif
                        </select>
                        <x-form.error name="location_id"/>
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_location_id"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                </div>

                {{-- Purchase cost + Order number bulk edits. Assets
                     have no per-row currency column, so if the
                     selection contains assets whose original orders
                     were in different currencies, one numeric value
                     written here reads as the system default currency
                     for every row regardless. Behavior is preserved
                     from the pre-orders-refactor era and callers are
                     expected to narrow the selection to one currency
                     before using these fields. --}}
                <x-form.row
                    :label="trans('general.order_number')"
                    name="order_number"
                    type="text"
                    :maxlength="191"
                    input_div_class="col-md-7 col-sm-12"
                />

                <x-form.row
                    :label="trans('general.purchase_cost')"
                    name="purchase_cost"
                    type="text"
                    input_div_class="col-md-4 col-sm-12"
                />

                <x-input.supplier-select
                    :label="trans('general.supplier')"
                    name="supplier_id"
                    :selected="old('supplier_id')"
                />

                <x-input.company-select
                    :label="trans('general.company')"
                    name="company_id"
                    :selected="old('company_id')"
                />

                {{-- Warranty months with unit-suffix addon. Free-text addon
                     needs slot:input. --}}
                <x-form.row
                    :label="trans('admin/hardware/form.warranty')"
                    name="warranty_months"
                    input_div_class="col-md-3 text-right"
                >
                    <x-slot:input>
                        <div class="input-group">
                            <input class="col-md-3 form-control" maxlength="4" type="text" name="warranty_months" id="warranty_months" value="{{ old('warranty_months') }}" />
                            <span class="input-group-addon">{{ trans('admin/hardware/form.months') }}</span>
                        </div>
                    </x-slot:input>
                </x-form.row>

                {{-- Next audit date + inline null-toggle + extra help
                     block on its own row. Same inline-null reasoning as
                     the name/date rows above, plus the trailing help
                     block that doesn't fit x-form.row's help_text slot
                     (which renders inside the input column, not below
                     as its own grid row). --}}
                <div class="form-group{{ $errors->has('next_audit_date') ? ' has-error' : '' }}">
                    <label for="next_audit_date" class="col-md-3 control-label">{{ trans('general.next_audit_date') }}</label>
                    <div class="col-md-4">
                        <x-input.datepicker
                            name="next_audit_date"
                            value="{{ old('next_audit_date') }}"
                            placeholder="{{ trans('general.select_date') }}"
                        />
                        <x-form.error name="next_audit_date" />
                    </div>
                    <div class="col-md-5">
                        <x-form.checkbox-inline
                            name="null_next_audit_date"
                            :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                        />
                    </div>
                    <div class="col-md-8 col-md-offset-3">
                        <p class="help-block">{!! trans('general.next_audit_date_help') !!}</p>
                    </div>
                </div>

                {{-- Requestable (3-way radio with a section-header label) --}}
                <x-form.radio-row
                    name="requestable"
                    :label="trans('admin/hardware/form.requestable')"
                    :options="[
                        '1' => trans('general.yes'),
                        '0' => trans('general.no'),
                        '' => trans('general.do_not_change'),
                    ]"
                    selected=""
                    input_div_class="col-md-7"
                />

                <x-form.row
                    :label="trans('general.notes')"
                    name="notes"
                    type="textarea"
                />

                {{-- Set notes to null --}}
                <x-form.checkbox-row
                    name="null_notes"
                    :label="trans_choice('general.set_to_null', count($assets), ['selection_count' => count($assets)])"
                />

                @include('models/custom_fields_form_bulk_edit', ['fieldsets' => $fieldsets, 'selectionCount' => count($assets)])

                @foreach ($assets as $asset)
                    <input type="hidden" name="ids[]" value="{{ $asset }}">
                @endforeach

                <x-slot:customfooter>
                    <div class="text-right box-footer">
                        <x-button.submit class="btn-success" />
                    </div>
                </x-slot:customfooter>

            </x-box>
        </x-form>

    </x-container>

@stop

