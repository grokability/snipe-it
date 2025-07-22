@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.localization_title') }}
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


    <form method="POST" action="{{ route('settings.localization.save') }}" accept-charset="UTF-8" autocomplete="off" class="form-horizontal" role="form">
    <!-- CSRF Token -->
    {{csrf_field()}}

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
                        <div class="form-group {{ $errors->has('site_name') ? 'error' : '' }}">
                            <div class="col-md-3 col-xs-12">
                                <label for="site_name">{{ trans('admin/settings/general.default_language') }}</label>
                            </div>
                            <div class="col-md-5 col-xs-12">
                                <x-input.locale-select name="locale" :selected="old('locale', $setting->locale)" />

                                {!! $errors->first('locale', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                        <!-- name display format -->
                        <div class="form-group {{ $errors->has('name_display_format') ? 'error' : '' }}">
                            <div class="col-md-3 col-xs-12">
                                <label for="name_display_format">{{ trans('general.name_display_format') }}</label>
                            </div>
                            <div class="col-md-5 col-xs-12">
                                {!! Form::name_display_format('name_display_format', old('name_display_format', $setting->name_display_format), 'select2') !!}

                                {!! $errors->first('name_display_format', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>



                        <!-- Date format -->
                        <div class="form-group {{ $errors->has('time_display_format') ? 'error' : '' }}">
                            <div class="col-md-3 col-xs-12">
                                <label for="time_display_format">{{ trans('general.time_and_date_display') }}</label>
                            </div>
                            <div class="col-md-5 col-xs-12">
                                {!! Form::date_display_format('date_display_format', old('date_display_format', $setting->date_display_format), 'select2') !!}
                            </div>
                            <div class="col-md-3 col-xs-12">
                                {!! Form::time_display_format('time_display_format', old('time_display_format', $setting->time_display_format), 'select2') !!}
                            </div>
                            
                            {!! $errors->first('time_display_format', '<div class="col-md-9 col-md-offset-3"><span class="alert-msg" aria-hidden="true">:message</span> </div>') !!}

                        </div>

                        <!-- Currency -->
                        <div class="form-group {{ $errors->has('default_currency') ? 'error' : '' }}">
                            <div class="col-md-3 col-xs-12">
                                <label for="default_currency">{{ trans('admin/settings/general.default_currency') }}</label>
                            </div>
                            <div class="col-md-9 col-xs-12">
                                <input
                                    class="form-control select2-container"
                                    placeholder="USD"
                                    maxlength="3"
                                    style="width: 60px; display: inline-block; "
                                    name="default_currency"
                                    type="text"
                                    value="{{ old('default_currency', $setting->default_currency) }}"
                                    id="default_currency"
                                >

                                {!! Form::digit_separator('digit_separator', old('digit_separator', $setting->digit_separator), 'select2') !!}

                                {!! $errors->first('default_currency', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>


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

