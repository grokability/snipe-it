@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.alert_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>


    <form method="POST" action="{{ route('settings.alerts.save') }}" autocomplete="off" class="form-horizontal" role="form" id="create-form">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


                <div class="panel box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">
                            <x-icon type="bell"/> {{ trans('admin/settings/general.alerts') }}
                        </h2>
                    </div>

                    <div class="box-body">

                        <div class="col-md-12">

                            <fieldset name="alerts-general">
                                <x-form.legend>
                                    {{ trans('admin/settings/general.legends.general') }}
                                </x-form.legend>

                                <!-- Menu Alerts Enabled -->
                                <x-form.checkbox-row
                                    name="show_alerts_in_menu"
                                    :label="trans('admin/settings/general.show_alerts_in_menu')"
                                    :item="$setting"
                                />

                                <!-- Alerts Enabled -->
                                <x-form.checkbox-row
                                    name="alerts_enabled"
                                    :label="trans('admin/settings/general.alerts_enabled')"
                                    :item="$setting"
                                />
                            </fieldset>

                            <fieldset name="alert-addresses">
                                <x-form.legend>
                                    {{ trans('admin/settings/general.legends.email') }}
                                </x-form.legend>

                                <!-- Alert Email -->
                                <x-form.row
                                    name="alert_email"
                                    :item="$setting"
                                    :label="trans('admin/settings/general.alert_email')"
                                    :help_text="trans('admin/settings/general.alert_email_help')"
                                    placeholder="admin@yourcompany.com,it@yourcompany.com"
                                    input_icon="email"
                                    input_group_addon="right"
                                />

                                <!-- Admin CC Email -->
                                <x-form.row
                                    name="admin_cc_email"
                                    type="email"
                                    :item="$setting"
                                    :label="trans('admin/settings/general.admin_cc_email')"
                                    :help_text="trans('admin/settings/general.admin_cc_email_help')"
                                    placeholder="admin@yourcompany.com"
                                    input_icon="email"
                                    input_group_addon="right"
                                />

                                <x-form.radio-row
                                    name="admin_cc_always"
                                    :item="$setting"
                                    :options="[
                                        '1' => trans('admin/settings/general.admin_cc_always'),
                                        '0' => trans('admin/settings/general.admin_cc_when_acceptance_required'),
                                    ]"
                                />
                            </fieldset>

                            <fieldset name="alert-intervals">
                                <x-form.legend>
                                    {{ trans('admin/settings/general.legends.intervals') }}
                                </x-form.legend>

                                <!-- Inventory alert threshold -->
                                <x-form.row
                                    name="alert_threshold"
                                    :label="trans('admin/settings/general.alert_inv_threshold')"
                                >
                                    <x-slot:input>
                                        <x-input.text
                                            type="number"
                                            name="alert_threshold"
                                            :value="old('alert_threshold', $setting->alert_threshold)"
                                            placeholder="5"
                                            maxlength="3"
                                            style="width: 100px;"
                                        />
                                    </x-slot:input>
                                </x-form.row>

                                <!-- Inventory alert interval -->
                                <x-form.row
                                    name="alert_interval"
                                    :label="trans('admin/settings/general.alert_interval')"
                                    input_div_class="col-xs-10 col-sm-6 col-md-4 col-lg-3 col-xl-3"
                                >
                                    <x-slot:input>
                                        <div class="input-group">
                                            <x-input.text
                                                type="number"
                                                name="alert_interval"
                                                :value="old('alert_interval', $setting->alert_interval)"
                                                placeholder="30"
                                                maxlength="3"
                                            />
                                            <span class="input-group-addon">{{ trans('general.days') }}</span>
                                        </div>
                                    </x-slot:input>
                                </x-form.row>

                                <!-- Due for checkin days -->
                                <x-form.row
                                    name="due_checkin_days"
                                    :label="trans('admin/settings/general.due_checkin_days')"
                                    :help_text="trans('admin/settings/general.due_checkin_days_help')"
                                    input_div_class="col-xs-10 col-sm-6 col-md-4 col-lg-3 col-xl-3"
                                >
                                    <x-slot:input>
                                        <div class="input-group">
                                            <x-input.text
                                                type="number"
                                                name="due_checkin_days"
                                                :value="old('due_checkin_days', $setting->due_checkin_days)"
                                                placeholder="14"
                                                maxlength="3"
                                            />
                                            <span class="input-group-addon">{{ trans('general.days') }}</span>
                                        </div>
                                    </x-slot:input>
                                </x-form.row>

                                <!-- Alert warning threshold -->
                                <x-form.row
                                    name="audit_warning_days"
                                    :label="trans('admin/settings/general.audit_warning_days')"
                                    :help_text="trans('admin/settings/general.audit_warning_days_help')"
                                    input_div_class="col-xs-10 col-sm-6 col-md-4 col-lg-3 col-xl-3"
                                >
                                    <x-slot:input>
                                        <div class="input-group">
                                            <x-input.text
                                                type="number"
                                                name="audit_warning_days"
                                                :value="old('audit_warning_days', $setting->audit_warning_days)"
                                                placeholder="14"
                                                maxlength="3"
                                                min="0"
                                            />
                                            <span class="input-group-addon">{{ trans('general.days') }}</span>
                                        </div>
                                    </x-slot:input>
                                </x-form.row>

                                <!-- Audit interval -->
                                <x-form.row
                                    name="audit_interval"
                                    :label="trans('admin/settings/general.audit_interval')"
                                    :help_text="trans('admin/settings/general.audit_interval_help')"
                                    input_div_class="col-xs-10 col-sm-6 col-md-6 col-lg-3 col-xl-3"
                                >
                                    <x-slot:input>
                                        <div class="input-group">
                                            <x-input.text
                                                type="number"
                                                name="audit_interval"
                                                :value="old('audit_interval', $setting->audit_interval)"
                                                placeholder="12"
                                                maxlength="3"
                                            />
                                            <span class="input-group-addon">{{ trans('general.months') }}</span>
                                        </div>
                                    </x-slot:input>
                                </x-form.row>

                                <!-- Update existing dates -->
                                <x-form.checkbox-row
                                    name="update_existing_dates"
                                    :label="trans('admin/settings/general.update_existing_dates')"
                                />
                            </fieldset>

                        </div>

                    </div> <!--/.box-body-->
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                        </div>
                    </div>
                </div> <!-- /box -->
            </div> <!-- /.col-md-8-->
        </div>

    </form>

@stop
