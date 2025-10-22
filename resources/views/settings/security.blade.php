@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.security_title') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')



    <form method="POST" autocomplete="off" class="form-horizontal" role="form" id="create-form">

    <!-- CSRF Token -->
    {{ csrf_field() }}

    <div class="row">
        <div class="col-sm-9 col-sm-offset-1 col-md-9 col-md-offset-2">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="locked"/>
                        {{ trans('admin/settings/general.security') }}
                    </h2>
                </div>
                <div class="box-body">


                    <div class="col-md-12">

                        <fieldset name="password-preferences">
                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.security') }}
                            </x-form-legend>

                            <!-- Two Factor -->
                            <div class="form-group {{ $errors->has('brand') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="two_factor_enabled">{{ trans('admin/settings/general.two_factor_enabled_text') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <x-input.select
                                        name="two_factor_enabled"
                                        :selected="old('two_factor_enabled', $setting->two_factor_enabled)"
                                        :options="[
                                            '' => trans('admin/settings/general.two_factor_disabled'),
                                            '1' => trans('admin/settings/general.two_factor_optional'),
                                            '2' => trans('admin/settings/general.two_factor_required'),
                                        ]"
                                    />
                                    <p class="help-block">{{ trans('admin/settings/general.two_factor_enabled_warning') }}</p>

                                    @if (config('app.lock_passwords'))
                                        <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                                    @endif

                                    {!! $errors->first('two_factor_enabled', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>

                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.passwords') }}
                            </x-form-legend>

                            <!-- Min characters -->
                            <div class="form-group {{ $errors->has('pwd_secure_min') ? 'error' : '' }}">
                                <div class="col-md-3 text-right  text-right">
                                    <label for="pwd_secure_min">{{ trans('admin/settings/general.pwd_secure_min') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" style="width: 60px;" name="pwd_secure_min" type="number" value="{{ old('pwd_secure_min', $setting->pwd_secure_min) }}" id="pwd_secure_min" maxlength="2" min="8">
                                    {!! $errors->first('pwd_secure_min', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    <p class="help-block">
                                        {{ trans('admin/settings/general.pwd_secure_min_help') }}
                                    </p>
                                </div>
                            </div>



                            <!-- Common Passwords -->
                            <div class="form-group {{ $errors->has('pwd_secure_complexity.*') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="pwd_secure_complexity">{{ trans('admin/settings/general.pwd_secure_complexity') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-control">
                                        <input type="checkbox" name="pwd_secure_uncommon" value="1" @checked(old('pwd_secure_uncommon', $setting->pwd_secure_uncommon)) aria-label="pwd_secure_uncommon"/>
                                        {{ trans('admin/settings/general.pwd_secure_uncommon') }}
                                    </label>
                                    <label class="form-control">
                                        <input type="checkbox" name="pwd_secure_complexity['disallow_same_pwd_as_user_fields']" value="disallow_same_pwd_as_user_fields" @checked(old('disallow_same_pwd_as_user_fields', strpos($setting->pwd_secure_complexity, 'disallow_same_pwd_as_user_fields')!==false)) aria-label="pwd_secure_complexity"/>
                                        {{ trans('admin/settings/general.pwd_secure_complexity_disallow_same_pwd_as_user_fields') }}
                                    </label>
                                    <label class="form-control">
                                        <input type="checkbox" name="pwd_secure_complexity['letters']" value="letters" @checked(old('pwd_secure_uncommon', strpos($setting->pwd_secure_complexity, 'letters')!==false)) aria-label="pwd_secure_complexity"/>
                                        {{ trans('admin/settings/general.pwd_secure_complexity_letters') }}
                                    </label>
                                    <label class="form-control">
                                        <input type="checkbox" name="pwd_secure_complexity['numbers']" value="numbers" @checked(old('pwd_secure_uncommon', strpos($setting->pwd_secure_complexity, 'numbers')!==false)) aria-label="pwd_secure_complexity"/>
                                        {{ trans('admin/settings/general.pwd_secure_complexity_numbers') }}
                                    </label>
                                    <label class="form-control">
                                        <input type="checkbox" name="pwd_secure_complexity['symbols']" value="symbols" @checked(old('pwd_secure_uncommon', strpos($setting->pwd_secure_complexity, 'symbols')!==false)) aria-label="pwd_secure_complexity"/>
                                        {{ trans('admin/settings/general.pwd_secure_complexity_symbols') }}
                                    </label>
                                    <label class="form-control">
                                        <input type="checkbox" name="pwd_secure_complexity['case_diff']" value="case_diff" @checked(old('pwd_secure_uncommon', strpos($setting->pwd_secure_complexity, 'case_diff')!==false)) aria-label="pwd_secure_complexity"/>
                                        {{ trans('admin/settings/general.pwd_secure_complexity_case_diff') }}
                                    </label>

                                    @if ($errors->has('pwd_secure_complexity.*'))
                                        <span class="alert-msg">{{ trans('validation.generic.invalid_value_in_field') }}</span>
                                    @endif
                                    <p class="help-block">
                                        {{ trans('admin/settings/general.pwd_secure_complexity_help') }}
                                    </p>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset name="remote-login">
                            <x-form-legend>
                                {{ trans('admin/settings/general.login_remote_user_text') }}
                            </x-form-legend>
                            <!-- Remote User Authentication -->
                            <div class="form-group {{ $errors->has('login_remote_user') ? 'error' : '' }}">

                                <div class="col-md-9 col-md-offset-3">
                                    <!--  Enable Remote User Login -->

                                    @if (config('app.lock_passwords'))
                                        <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                                    @else
                                        <label class="form-control">
                                            <input type="checkbox" name="login_remote_user_enabled" value="1" @checked(old('login_remote_user_enabled', $setting->login_remote_user_enabled)) aria-label="login_remote_user"/>
                                            <label for="login_remote_user_enabled">{{ trans('admin/settings/general.login_remote_user_enabled_text') }}</label>
                                        </label>

                                        {!! $errors->first('login_remote_user_enabled', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                        <p class="help-block">
                                            {{ trans('admin/settings/general.login_remote_user_enabled_help') }}
                                        </p>
                                </div>
                                <div class="col-md-3 text-right">
                                        <!-- Use custom remote user header name -->
                                        <label for="login_remote_user_header_name">{{ trans('admin/settings/general.login_remote_user_header_name_text') }}</label>
                                </div>
                                <div class="col-md-9">
                                        <input class="form-control" name="login_remote_user_header_name" type="text" value="{{ old('login_remote_user_header_name', $setting->login_remote_user_header_name) }}" id="login_remote_user_header_name">
                                        {!! $errors->first('login_remote_user_header_name', '<span class="alert-msg">:message</span>') !!}
                                        <p class="help-block">
                                            {{ trans('admin/settings/general.login_remote_user_header_name_help') }}
                                        </p>
                                </div>
                                <div class="col-md-3 text-right">
                                        <!-- Custom logout url to redirect to authentication provider -->
                                        <label for="login_remote_user_custom_logout_url">{{ trans('admin/settings/general.login_remote_user_custom_logout_url_text') }}</label>
                                </div>
                                <div class="col-md-9">
                                        <input class="form-control" aria-label="login_remote_user_custom_logout_url" name="login_remote_user_custom_logout_url" type="url" value="{{ old('login_remote_user_custom_logout_url', $setting->login_remote_user_custom_logout_url) }}" id="login_remote_user_custom_logout_url">
                                        {!! $errors->first('login_remote_user_custom_logout_url', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                        <p class="help-block">
                                            {{ trans('admin/settings/general.login_remote_user_custom_logout_url_help') }}
                                        </p>
                                </div>
                                @if ($setting->login_remote_user_enabled == '1')
                                    <div class="col-md-3 text-right">
                                        <!--  Disable other logins mechanism -->
                                        <label class="form-control">
                                            <input type="checkbox" name="login_common_disabled" value="1" @checked(old('login_common_disabled', $setting->login_common_disabled)) aria-label="login_common_disabled"/>
                                            {{ trans('admin/settings/general.login_common_disabled_text') }}
                                        </label>
                                    </div>
                                    <div class="col-md-9">
                                        {!! $errors->first('login_common_disabled', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                        <p class="help-block">
                                            {{ trans('admin/settings/general.login_common_disabled_help') }}
                                        </p>
                                        @endif
                                    </div>

                                @endif
                        </fieldset>



                    </div>

                </div> <!--/.box-body-->
                <div class="box-footer">
                    <div class="text-left col-md-6">
                        <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                    </div>
                    <div class="text-right col-md-6">
                        <button type="submit" class="btn btn-success"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                    </div>

                </div>
            </div> <!-- /box -->
        </div> <!-- /.col-md-8-->
    </div> <!-- /.row-->

    </form>

@stop
