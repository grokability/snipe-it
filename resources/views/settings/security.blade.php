@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.security_title') }}
    @parent
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
                                <x-form.legend>
                                    {{ trans('admin/settings/general.legends.security') }}
                                </x-form.legend>

                                <!-- Two Factor -->
                                <x-form.row
                                    name="two_factor_enabled"
                                    :label="trans('admin/settings/general.two_factor_enabled_text')"
                                    :help_text="trans('admin/settings/general.two_factor_enabled_warning')"
                                >
                                    <x-slot:input>
                                        <x-input.select
                                            name="two_factor_enabled"
                                            :selected="old('two_factor_enabled', $setting->two_factor_enabled)"
                                            :options="[
                                                '' => trans('admin/settings/general.two_factor_disabled'),
                                                '1' => trans('admin/settings/general.two_factor_optional'),
                                                '2' => trans('admin/settings/general.two_factor_required'),
                                            ]"
                                            :disabled="config('app.lock_passwords')"
                                        />
                                        <div>
                                            <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
                                        </div>
                                    </x-slot:input>
                                </x-form.row>

                                <x-form.legend>
                                    {{ trans('admin/settings/general.legends.passwords') }}
                                </x-form.legend>

                                <!-- Min characters -->
                                <x-form.row
                                    name="pwd_secure_min"
                                    :label="trans('admin/settings/general.pwd_secure_min')"
                                    :help_text="trans('admin/settings/general.pwd_secure_min_help')"
                                >
                                    <x-slot:input>
                                        <x-input.text
                                            type="number"
                                            name="pwd_secure_min"
                                            :value="old('pwd_secure_min', $setting->pwd_secure_min)"
                                            style="width: 60px;"
                                            maxlength="2"
                                            min="8"
                                        />
                                    </x-slot:input>
                                </x-form.row>

                                <!-- Password complexity -->
                                <x-form.row
                                    name="pwd_secure_complexity"
                                    :label="trans('admin/settings/general.pwd_secure_complexity')"
                                    :help_text="trans('admin/settings/general.pwd_secure_complexity_help')"
                                >
                                    <x-slot:input>
                                        <label class="form-control">
                                            <x-input.checkbox
                                                name="pwd_secure_uncommon"
                                                :checked="(bool) old('pwd_secure_uncommon', $setting->pwd_secure_uncommon)"
                                                aria-label="pwd_secure_uncommon"
                                            />
                                            {{ trans('admin/settings/general.pwd_secure_uncommon') }}
                                        </label>
                                        @foreach ([
                                            'disallow_same_pwd_as_user_fields' => trans('admin/settings/general.pwd_secure_complexity_disallow_same_pwd_as_user_fields'),
                                            'letters' => trans('admin/settings/general.pwd_secure_complexity_letters'),
                                            'numbers' => trans('admin/settings/general.pwd_secure_complexity_numbers'),
                                            'symbols' => trans('admin/settings/general.pwd_secure_complexity_symbols'),
                                            'case_diff' => trans('admin/settings/general.pwd_secure_complexity_case_diff'),
                                        ] as $complexity_value => $complexity_label)
                                            <label class="form-control">
                                                <x-input.checkbox
                                                    name="pwd_secure_complexity[]"
                                                    :value="$complexity_value"
                                                    :checked="str_contains($setting->pwd_secure_complexity ?? '', $complexity_value)"
                                                    aria-label="pwd_secure_complexity"
                                                />
                                                {{ $complexity_label }}
                                            </label>
                                        @endforeach

                                        @if ($errors->has('pwd_secure_complexity.*'))
                                            <span class="alert-msg" role="alert" aria-live="assertive">{{ trans('validation.generic.invalid_value_in_field') }}</span>
                                        @endif
                                    </x-slot:input>
                                </x-form.row>
                            </fieldset>

                            <fieldset name="remote-login">
                                <x-form.legend help_text="{{ trans('admin/settings/general.remote_user_legend_warning') }}" icon="warning" class="text-danger">
                                    {{ trans('admin/settings/general.login_remote_user_text') }}
                                </x-form.legend>

                                {{-- Remote-header auth is an exception to the rest of the
                                     demo-lock pattern: even a visible-but-disabled control
                                     could be misread as "you can turn this on later," so
                                     the whole section is hidden in demo mode and we show
                                     a single feature-disabled notice instead. --}}
                                @if (config('app.lock_passwords'))
                                    <x-demo-lock class="col-md-4 col-md-offset-3">{{ trans('general.feature_disabled') }}</x-demo-lock>
                                @else
                                    <!-- Enable Remote User Login -->
                                    <x-form.row
                                        name="login_remote_user_enabled"
                                        input_div_class="col-md-8 col-md-offset-3"
                                    >
                                        <x-slot:input>
                                            <label class="form-control">
                                                <x-input.checkbox
                                                    name="login_remote_user_enabled"
                                                    :checked="(bool) old('login_remote_user_enabled', $setting->login_remote_user_enabled)"
                                                    aria-label="login_remote_user"
                                                />
                                                {{ trans('admin/settings/general.login_remote_user_enabled_text') }}
                                            </label>
                                            <p class="help-block">
                                                {{ trans('admin/settings/general.login_remote_user_enabled_help') }}
                                            </p>
                                        </x-slot:input>
                                    </x-form.row>

                                    <!-- Use custom remote user header name -->
                                    <x-form.row
                                        name="login_remote_user_header_name"
                                        :label="trans('admin/settings/general.login_remote_user_header_name_text')"
                                        :help_html="trans('admin/settings/general.login_remote_user_header_name_help')"
                                        help_icon="warning"
                                    >
                                        <x-slot:input>
                                            <x-input.text
                                                name="login_remote_user_header_name"
                                                :value="old('login_remote_user_header_name', $setting->login_remote_user_header_name)"
                                            />
                                        </x-slot:input>
                                    </x-form.row>

                                    <!-- Custom logout url to redirect to authentication provider -->
                                    <x-form.row
                                        name="login_remote_user_custom_logout_url"
                                        :label="trans('admin/settings/general.login_remote_user_custom_logout_url_text')"
                                        :help_text="trans('admin/settings/general.login_remote_user_custom_logout_url_help')"
                                    >
                                        <x-slot:input>
                                            <x-input.text
                                                type="url"
                                                name="login_remote_user_custom_logout_url"
                                                :value="old('login_remote_user_custom_logout_url', $setting->login_remote_user_custom_logout_url)"
                                                aria-label="login_remote_user_custom_logout_url"
                                            />
                                        </x-slot:input>
                                    </x-form.row>

                                    <!-- Disable other logins mechanism -->
                                    @if ($setting->login_remote_user_enabled == '1')
                                        <x-form.row
                                            name="login_common_disabled"
                                            input_div_class="col-md-8 col-md-offset-3"
                                        >
                                            <x-slot:input>
                                                <label class="form-control">
                                                    <x-input.checkbox
                                                        name="login_common_disabled"
                                                        :checked="(bool) old('login_common_disabled', $setting->login_common_disabled)"
                                                        aria-label="login_common_disabled"
                                                    />
                                                    {{ trans('admin/settings/general.login_common_disabled_text') }}
                                                </label>
                                                <p class="help-block">
                                                    {{ trans('admin/settings/general.login_common_disabled_help') }}
                                                </p>
                                            </x-slot:input>
                                        </x-form.row>
                                    @endif
                                @endif
                            </fieldset>

                        </div>

                    </div> <!--/.box-body-->
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <button type="submit" class="btn btn-success">
                                <x-icon type="checkmark"/> {{ trans('general.save') }}</button>
                        </div>

                    </div>
                </div> <!-- /box -->
            </div> <!-- /.col-md-8-->
        </div> <!-- /.row-->

    </form>

@stop
