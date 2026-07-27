@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.bulk_checkout') }}
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

            <x-form id="checkout_form" route="{{ url()->current() }}" data-disable-empty-on-submit data-autofocus-select2-search>

                <x-box header="{{ trans('admin/hardware/form.tag') }}">

                    @include ('partials.forms.edit.asset-select', [
                        'translated_name' => trans('general.assets'),
                        'fieldname' => 'selected_assets[]',
                        'multiple' => true,
                        'required' => true,
                        'asset_status_type' => 'RTD',
                        'select_id' => 'assigned_assets_select',
                        'asset_selector_div_id' => 'assets_to_checkout_div',
                        'asset_ids' => old('selected_assets'),
                    ])

                    <x-form.row
                        :label="trans('admin/hardware/form.status')"
                        name="status_id"
                    >
                        <x-slot:input>
                            <x-input.select
                                name="status_id"
                                :options="$statusLabel_list"
                                :selected="old('status_id', $status_id ?? null)"
                                style="width: 100%;"
                                aria-label="status_id"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <x-form.row
                        :label="trans('admin/hardware/form.requestable')"
                        name="set_not_requestable"
                    >
                        <x-slot:input>
                            <x-input.select
                                name="set_not_requestable"
                                id="set_not_requestable"
                                :options="[
                                    '' => trans('general.do_not_change'),
                                    '1' => trans('admin/hardware/general.not_requestable'),
                                ]"
                                :selected="old('set_not_requestable', '')"
                                style="width: 100%;"
                                aria-label="set_not_requestable"
                            />
                        </x-slot:input>
                    </x-form.row>

                    @include ('partials.forms.checkout-selector', ['user_select' => 'true', 'asset_select' => 'true', 'location_select' => 'true'])
                    <x-input.user-select
                        :label="trans('general.user')"
                        name="assigned_user"
                        :selected="old('assigned_user')"
                    />
                    <!-- unselect keeps the asset being checked out from being pre-selected in this picker -->
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'asset_selector_div_id' => 'assigned_asset', 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])

                    <x-form.row
                        :label="trans('admin/hardware/form.checkout_date')"
                        name="checkout_at"
                        type="datetimepicker"
                        input_div_class="col-md-4"
                    />

                    <x-form.row
                        :label="trans('admin/hardware/form.expected_checkin')"
                        name="expected_checkin"
                        type="datetimepicker"
                        :default_now="false"
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
                        <div class="box-footer">
                            <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                            <button type="submit" class="btn btn-primary pull-right"><x-icon type="checkmark"/> {{ trans('general.checkout') }}</button>
                        </div>
                    </x-slot:customfooter>

                </x-box>

            </x-form>

        </x-page-column>

        <x-page-column class="col-md-5">
            <x-side-panel.removed-assets
                :items="$removed_assets"
                :message="trans('general.assigned_assets_removed')"
            />
            <livewire:checkout-target-panel type="assets" />
        </x-page-column>

    </x-container>
@stop

