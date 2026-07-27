@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.localization_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>


    <form method="POST" action="{{ route('settings.localization.save') }}" accept-charset="UTF-8" autocomplete="off" class="form-horizontal" role="form">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


                <div class="panel box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">
                            <x-icon type="globe-us" /> {{ trans('admin/settings/general.localization') }}
                        </h2>
                    </div>

                    <div class="box-body">

                        <div class="col-md-12">

                            <!-- Language -->
                            <x-form.row
                                name="locale"
                                :label="trans('admin/settings/general.default_language')"
                            >
                                <x-slot:input>
                                    <x-input.locale-select name="locale" :selected="old('locale', $setting->locale)" />
                                </x-slot:input>
                            </x-form.row>

                            <!-- Name display format -->
                            <x-form.row
                                name="name_display_format"
                                :label="trans('general.name_display_format')"
                            >
                                <x-slot:input>
                                    <x-input.select
                                        name="name_display_format"
                                        :options="[
                                            'first_last' => trans('general.firstname_lastname_display'),
                                            'last_first' => trans('general.lastname_firstname_display'),
                                        ]"
                                        :selected="old('name_display_format', $setting->name_display_format)"
                                        style="width: 100%"
                                    />
                                </x-slot:input>
                            </x-form.row>

                            <!-- Date + time display format -->
                            <x-form.row
                                name="date_display_format"
                                :label="trans('general.time_and_date_display')"
                            >
                                <x-slot:input>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <x-input.date-display-format
                                                name="date_display_format"
                                                :selected="old('date_display_format', $setting->date_display_format)"
                                                style="min-width:100%"
                                                aria-label="date_display_format"
                                            />
                                        </div>
                                        <div class="col-md-5">
                                            <x-input.time-display-format
                                                name="time_display_format"
                                                :selected="old('time_display_format', $setting->time_display_format)"
                                                style="min-width:100%"
                                                aria-label="time_display_format"
                                            />
                                        </div>
                                    </div>
                                    <x-form.error name="time_display_format" />
                                </x-slot:input>
                            </x-form.row>

                            <!-- Week start -->
                            <x-form.row
                                name="week_start"
                                :label="trans('datepicker.week_start')"
                            >
                                <x-slot:input>
                                    <x-input.select
                                        name="week_start"
                                        :options="[
                                            '0' => trans('datepicker.days.sunday'),
                                            '1' => trans('datepicker.days.monday'),
                                            '2' => trans('datepicker.days.tuesday'),
                                            '3' => trans('datepicker.days.wednesday'),
                                            '4' => trans('datepicker.days.thursday'),
                                            '5' => trans('datepicker.days.friday'),
                                            '6' => trans('datepicker.days.saturday'),
                                        ]"
                                        :selected="old('week_start', $setting->week_start)"
                                        style="width: 100%"
                                        aria-label="week_start"
                                    />
                                </x-slot:input>
                            </x-form.row>

                            <!-- Currency + digit separator -->
                            <x-form.row
                                name="default_currency"
                                :label="trans('admin/settings/general.default_currency')"
                                input_div_class="col-md-9"
                            >
                                <x-slot:input>
                                    <x-input.text
                                        name="default_currency"
                                        :value="old('default_currency', $setting->default_currency)"
                                        placeholder="USD"
                                        maxlength="3"
                                        style="width: 60px; display: inline-block; vertical-align: middle;"
                                    />

                                    <x-input.select
                                        name="digit_separator"
                                        :options="['1,234.56' => '1,234.56', '1.234,56' => '1.234,56']"
                                        :selected="old('digit_separator', $setting->digit_separator)"
                                        style="min-width:120px"
                                    />
                                </x-slot:input>
                            </x-form.row>

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
