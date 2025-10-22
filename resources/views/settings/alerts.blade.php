@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.alert_title') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>


    <form method="POST" action="{{ route('settings.alerts.save') }}" autocomplete="off" class="form-horizontal" role="form" id="create-form">

    <!-- CSRF Token -->
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


                        <fieldset name="remote-login"">
                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.general') }}
                            </x-form-legend>

                            <!-- Menu Alerts Enabled -->
                            <div class="form-group {{ $errors->has('show_alerts_in_menu') ? 'error' : '' }}">
                                <div class="col-md-9 col-md-offset-3">
                                    <label class="form-control">
                                        <input type="checkbox" name="show_alerts_in_menu" value="1" @checked(old('show_alerts_in_menu', $setting->show_alerts_in_menu))>
                                        {{ trans('admin/settings/general.show_alerts_in_menu') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Alerts Enabled -->
                            <div class="form-group {{ $errors->has('alerts_enabled') ? 'error' : '' }}">
                                <div class="col-md-9 col-md-offset-3">
                                    <label class="form-control">
                                        <input type="checkbox" name="alerts_enabled" value="1" @checked(old('alerts_enabled', $setting->alerts_enabled))>
                                        {{  trans('admin/settings/general.alerts_enabled') }}
                                    </label>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset name="alert-addresses"">
                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.email') }}
                            </x-form-legend>

                            <!-- Alert Email -->
                            <div class="form-group {{ $errors->has('alert_email') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="alert_email">{{ trans('admin/settings/general.alert_email') }}</label>
                                </div>
                                <div class="col-md-8 input-group">
                                    <input type="text" name="alert_email" value="{{ old('alert_email', $setting->alert_email) }}" class="form-control" placeholder="admin@yourcompany.com,it@yourcompany.com" maxlength="191">
                                    <span class="input-group-addon">
                                        <x-icon type="email" />
                                    </span>
                                </div>
                                <div class="col-md-8 col-md-offset-3">
                                    <p class="help-block">{{ trans('admin/settings/general.alert_email_help') }}</p>
                                    {!! $errors->first('alert_email', '<span class="alert-msg" aria-hidden="true">:message</span><br>') !!}
                                </div>
                            </div>


                            <!-- Admin CC Email -->
                            <div class="form-group {{ $errors->has('admin_cc_email') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="admin_cc_email">{{ trans('admin/settings/general.admin_cc_email') }}</label>
                                </div>
                                <div class="col-md-8 input-group">
                                    <input type="email" name="admin_cc_email" value="{{ old('admin_cc_email', $setting->admin_cc_email) }}" class="form-control" placeholder="admin@yourcompany.com" maxlength="191">
                                    <span class="input-group-addon">
                                        <x-icon type="email" />
                                    </span>
                                </div>
                                <div class="col-md-8 col-md-offset-3">
                                    <p class="help-block">{{ trans('admin/settings/general.admin_cc_email_help') }}</p>
                                    {!! $errors->first('admin_cc_email', '<span class="alert-msg" aria-hidden="true">:message</span><br>') !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-3">
                                    <label class="form-control">
                                        <input
                                            type="radio"
                                            name="admin_cc_always"
                                            value="1"
                                            @checked($setting->admin_cc_always == 1)
                                        >
                                        {{ trans('admin/settings/general.admin_cc_always') }}
                                    </label>
                                    <label class="form-control">
                                        <input
                                            type="radio"
                                            name="admin_cc_always"
                                            value="0"
                                            @checked($setting->admin_cc_always == 0)
                                        >
                                        {{ trans('admin/settings/general.admin_cc_when_acceptance_required') }}
                                    </label>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset name="remote-login"">

                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.intervals') }}
                            </x-form-legend>

                            <!-- Inventory alert threshold -->
                            <div class="form-group {{ $errors->has('alert_threshold') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="alert_threshold">{{ trans('admin/settings/general.alert_inv_threshold') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="5" maxlength="3" style="width: 100px;" name="alert_threshold" type="number" value="{{ old('alert_threshold', $setting->alert_threshold) }}" id="alert_threshold">
                                    {!! $errors->first('alert_threshold', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>


                            <!-- Inventory alert interval -->
                            <div class="form-group {{ $errors->has('alert_interval') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="alert_interval">{{ trans('admin/settings/general.alert_interval') }}</label>
                                </div>
                                <div class="input-group col-xs-10 col-sm-6 col-md-4 col-lg-3 col-xl-3">
                                    <input class="form-control" placeholder="30" maxlength="3" name="alert_interval" type="number" value="{{ old('alert_interval', $setting->alert_interval) }}" id="alert_interval">
                                    <span class="input-group-addon">{{ trans('general.days') }}</span>
                                    {!! $errors->first('alert_interval', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>


                            <!-- Due for checkin days -->
                            <div class="form-group {{ $errors->has('due_checkin_days') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="due_checkin_days">{{ trans('admin/settings/general.due_checkin_days') }}</label>
                                </div>
                                <div class="input-group col-xs-10 col-sm-6 col-md-4 col-lg-3 col-xl-3">
                                    <input class="form-control" placeholder="14" maxlength="3" name="due_checkin_days" type="number" id="due_checkin_days" value="{{ old('due_checkin_days', $setting->due_checkin_days) }}">
                                    <span class="input-group-addon">{{ trans('general.days') }}</span>
                                </div>
                                <div class="col-md-8 col-md-offset-3">
                                    {!! $errors->first('due_checkin_days', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    <p class="help-block">{{ trans('admin/settings/general.due_checkin_days_help') }}</p>
                                </div>
                            </div>

                            <!-- Alert warning threshold -->
                            <div class="form-group {{ $errors->has('audit_warning_days') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="audit_warning_days">{{ trans('admin/settings/general.audit_warning_days') }}</label>
                                </div>
                                <div class="input-group col-xs-10 col-sm-6 col-md-4 col-lg-3 col-xl-3">
                                    <input class="form-control" placeholder="14" maxlength="3" name="audit_warning_days" type="number" id="audit_warning_days" value="{{ old('audit_warning_days', $setting->audit_warning_days) }}">
                                    <span class="input-group-addon">{{ trans('general.days') }}</span>
                                </div>
                                <div class="col-md-8 col-md-offset-3">
                                    {!! $errors->first('audit_warning_days', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    <p class="help-block">{{ trans('admin/settings/general.audit_warning_days_help') }}</p>
                                </div>
                            </div>

                            <!-- Audit interval -->
                            <div class="form-group {{ $errors->has('audit_interval') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="audit_interval">{{ trans('admin/settings/general.audit_interval') }}</label>
                                </div>
                                <div class="input-group col-xs-10 col-sm-6 col-md-6 col-lg-3 col-xl-3">
                                    <input class="form-control" placeholder="12" maxlength="3" name="audit_interval" type="number" id="audit_interval" value="{{ old('audit_interval', $setting->audit_interval) }}">
                                    <span class="input-group-addon">{{ trans('general.months') }}</span>
                                </div>
                                <div class="col-md-8 col-md-offset-3">
                                    {!! $errors->first('audit_interval', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    <p class="help-block">
                                        {{ trans('admin/settings/general.audit_interval_help') }}
                                    </p>
                                </div>
                            </div>

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
    </div> <!-- /.row-->

    </form>

@stop

