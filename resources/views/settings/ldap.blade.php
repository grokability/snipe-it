@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.ldap_ad') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-default"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }

        span.nullval {
            color: darkgrey;
            font-style: italic;
        }

        /*
           Don't make the password field *look* readonly - this is for usability, so admins don't think they can't edit this field.
         */
        .form-control[readonly] {
            background-color: white;
            color: #555555;
            cursor:text;
        }

        .table-wrapper {
            overflow-x: auto;
            display: block;
            background-color: white;
        }

    </style>

    @if ((!function_exists('ldap_connect')) || (!function_exists('ldap_set_option')) || (!function_exists('ldap_bind')))
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        {{ trans('admin/settings/general.ldap_extension_warning') }}
                    </div>
                </div>
            </div>
        </div>

    @endif

    <form method="POST"  action="{{ route('settings.ldap.save') }}" autocomplete="off" class="form-horizontal" role="form" id="create-form">
    <!-- CSRF Token -->
    {{csrf_field()}}

    <input type="hidden" name="username" value="{{ old('username', $user->username) }}">

    <!-- this is a hack to prevent Chrome from trying to autocomplete fields -->
    <input type="text" name="prevent_autofill" id="prevent_autofill" value="" style="display:none;" />
    <input type="password" name="password_fake" id="password_fake" value="" style="display:none;" />

    <div class="row">
        <div class="col-md-10 col-md-offset-1">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="ldap"/>
                        {{ trans('admin/settings/general.ldap_ad') }}
                    </h2>
                </div>
                <div class="box-body">

                    <div class="col-md-12">

                        <fieldset>
                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.server') }}
                            </x-form-legend>
                                <!-- Enable LDAP -->
                                <div class="form-group {{ $errors->has('ldap_integration') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_enabled">{{ trans('admin/settings/general.ldap_integration') }}</label>
                                    </div>
                                    <div class="col-md-8">

                                        <label class="form-control">
                                            <input type="checkbox" name="ldap_enabled" value="1" id="ldap_enabled" @checked(old('ldap_enabled', $setting->ldap_enabled)) />
                                        {{ trans('admin/settings/general.ldap_enabled') }}
                                        </label>

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>


                                <!-- AD Flag -->
                                <div class="form-group">
                                    <div class="col-md-3 text-right">
                                        <label for="is_ad">{{ trans('admin/settings/general.ad') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-control">
                                            <input type="checkbox" name="is_ad" value="1" id="is_ad" @checked(old('is_ad', $setting->is_ad))/>
                                        {{ trans('admin/settings/general.is_ad') }}
                                        </label>
                                        @error('is_ad')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- LDAP Password Sync -->
                                <div class="form-group">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_pw_sync">{{ trans('admin/settings/general.ldap_pw_sync') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-control">
                                            <input type="checkbox" name="ldap_pw_sync" value="1" id="ldap_pw_sync" @checked(old('ldap_pw_sync', $setting->ldap_pw_sync)) />
                                        {{ trans('general.yes') }}
                                        </label>

                                        <p class="help-block">{{ trans('admin/settings/general.ldap_pw_sync_help') }}</p>
                                        @error('ldap_pw_sync')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif

                                    </div>
                                </div>

                                <!-- AD Domain -->
                                <div class="form-group {{ $errors->has('ad_domain') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ad_domain">{{ trans('admin/settings/general.ad_domain') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control" placeholder="{{ trans('general.example') .'example.com' }}" name="ad_domain" type="text" id="ad_domain" value="{{ old('ad_domain', $setting->ad_domain) }}">
                                        <p class="help-block">{{ trans('admin/settings/general.ad_domain_help') }}</p>
                                        @error('ad_domain')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div><!-- AD Domain -->

                                <!-- LDAP Client-Side TLS key -->
                                <div class="form-group {{ $errors->has('ldap_client_tls_key') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_client_tls_key">
                                            {{ trans('admin/settings/general.ldap_client_tls_key') }}
                                        </label>
                                    </div>
                                    <div class="col-md-8">
                                        <x-input.textarea
                                            name="ldap_client_tls_key"
                                            value="{{ old('ldap_client_tls_key', $setting->ldap_client_tls_key) }}"
                                            :placeholder="sprintf('%s-----BEGIN RSA PRIVATE KEY-----%s1234567890%s-----END RSA PRIVATE KEY-----', trans('general.example'), PHP_EOL, PHP_EOL)"
                                        />
                                        @error('ldap_client_tls_key')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div><!-- LDAP Client-Side TLS key -->

                                <!-- LDAP Client-Side TLS certificate -->
                                <div class="form-group {{ $errors->has('ldap_client_tls_cert') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_client_tls_cert">{{ trans('admin/settings/general.ldap_client_tls_cert') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <x-input.textarea
                                            name="ldap_client_tls_cert"
                                            value="{{ old('ldap_client_tls_cert', $setting->ldap_client_tls_cert) }}"
                                            :placeholder="sprintf('%s-----BEGIN CERTIFICATE-----%s1234567890%s-----END CERTIFICATE-----', trans('general.example'), PHP_EOL, PHP_EOL)"
                                        />
                                        <p class="help-block">{{ trans('admin/settings/general.ldap_client_tls_cert_help') }}</p>
                                        @error('ldap_client_tls_cert')
                                            <span class="alert-msg">
                                               <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div><!-- LDAP Client-Side TLS certificate -->

                                <!-- LDAP Server -->
                                <div class="form-group {{ $errors->has('ldap_server') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_server">{{ trans('admin/settings/general.ldap_server') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control" placeholder="{{ trans('general.example') .'ldap://ldap.example.com' }}" name="ldap_server" type="text" id="ldap_server" value="{{ old('ldap_server', $setting->ldap_server) }}">
                                        @error('ldap_server')
                                            <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        <p class="help-block">{{ trans('admin/settings/general.ldap_server_help') }}</p>

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div><!-- LDAP Server -->

                                <!-- Start TLS -->
                                <div class="form-group">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_tls">{{ trans('admin/settings/general.ldap_tls') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-control">
                                            <input type="checkbox" name="ldap_tls" value="1" id="ldap_tls" @checked(old('ldap_tls', $setting->ldap_tls)) />
                                            {{ trans('admin/settings/general.ldap_tls_help') }}
                                        </label>
                                        @error('ldap_tls')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Ignore LDAP Certificate -->
                                <div class="form-group {{ $errors->has('ldap_server_cert_ignore') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_server_cert_ignore">{{ trans('admin/settings/general.ldap_server_cert') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-control">
                                            <input type="checkbox" name="ldap_server_cert_ignore" value="1" id="ldap_server_cert_ignore" @checked(old('ldap_server_cert_ignore', $setting->ldap_server_cert_ignore)) />
                                            {{ trans('admin/settings/general.ldap_server_cert_ignore') }}
                                        </label>
                                        @error('ldap_server_cert_ignore')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        <p class="help-block">
                                            {{ trans('admin/settings/general.ldap_server_cert_help') }}
                                        </p>
                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- LDAP Username -->
                                <div class="form-group {{ $errors->has('ldap_uname') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_uname">{{ trans('admin/settings/general.ldap_uname') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control" autocomplete="off" placeholder="{{ trans('general.example') .'binduser@example.com' }}" name="ldap_uname" type="text" id="ldap_uname" value="{{ old('ldap_uname', $setting->ldap_uname) }}">
                                        @error('ldap_uname')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- LDAP pword -->
                                <div class="form-group {{ $errors->has('ldap_pword') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_pword">{{ trans('admin/settings/general.ldap_pword') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control" type="password" name="ldap_pword" id="ldap_pword" value="" autocomplete="off" onfocus="this.removeAttribute('readonly');" readonly>
                                        @error('ldap_pword')
                                            <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- LDAP basedn -->
                                <div class="form-group {{ $errors->has('ldap_basedn') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_basedn">{{ trans('admin/settings/general.ldap_basedn') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control" placeholder="{{ trans('general.example') .'cn=users/authorized,dc=example,dc=com' }}" name="ldap_basedn" type="text" id="ldap_basedn" value="{{ old('ldap_basedn', $setting->ldap_basedn) }}">
                                        @error('ldap_basedn')
                                            <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- LDAP filter -->
                                <div class="form-group {{ $errors->has('ldap_filter') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="ldap_filter">{{ trans('admin/settings/general.ldap_filter') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="ldap_filter" id="ldap_filter" value="{{  old('ldap_filter', $setting->ldap_filter) }}" class="form-control" placeholder="{{  trans('general.example') .'&(cn=*)' }}">
                                        @error('ldap_filter')
                                            <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                            <!-- LDAP Auth Filter Query -->
                            <div class="form-group {{ $errors->has('ldap_auth_filter_query') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_auth_filter_query">{{ trans('admin/settings/general.ldap_auth_filter_query') }}</label>
                                </div>
                                <div class="col-md-8">

                                    <input type="text" name="ldap_auth_filter_query" id="ldap_auth_filter_query" value="{{  old('ldap_auth_filter_query', $setting->ldap_auth_filter_query) }}" class="form-control" placeholder="{{ trans('general.example') .'uid='  }}">
                                    @error('ldap_auth_filter_query')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {!! $message !!}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!--  Default LDAP Permissions Group Select -->

                            <div class="form-group{{ $errors->has('group') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_default_group">{{ trans('admin/settings/general.ldap_default_group') }}</label>
                                </div>

                                <div class="col-md-8">

                                    @if ($groups->count())
                                        @if ((Config::get('app.lock_passwords') || (!Auth::user()->isSuperUser())))
                                            <ul>
                                                @foreach ($groups as $id => $group)
                                                    {!! '<li>'.e($group).'</li>' !!}
                                                @endforeach
                                            </ul>

                                            <span class="help-block">{{ trans('admin/users/general.group_memberships_helpblock') }}</span>
                                        @else
                                            <div class="controls">
                                                <select name="ldap_default_group" aria-label="ldap_default_group" id="ldap_default_group" class="form-control select2">
                                                    <option value="">{{ trans('admin/settings/general.no_default_group') }}</option>
                                                    @foreach ($groups as $id => $group)
                                                        <option value="{{ $id }}" {{ $setting->ldap_default_group == $id ? 'selected' : '' }}>
                                                            {{ $group }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <span class="help-block">
                                                        {{ trans('admin/settings/general.ldap_default_group_info') }}
                                                    </span>
                                            </div>
                                        @endif
                                    @else
                                        <p>{!! trans('admin/settings/general.no_groups') !!}</p>
                                    @endif

                                </div>
                            </div>

                        </fieldset>

                        <fieldset>

                            <x-form-legend
                            help_text="{!! trans('admin/settings/general.ldap_mapping_help') !!}">
                                {{ trans('admin/settings/general.legends.mapping') }}
                            </x-form-legend>


                            <!-- LDAP  username field-->
                            <div class="form-group {{ $errors->has('ldap_username_field') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_username_field">{{ trans('admin/settings/general.ldap_username_field') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="ldap_username_field" id="ldap_username_field" value="{{  old('ldap_username_field', $setting->ldap_username_field) }}" class="form-control" placeholder="{{  trans('general.example') .'samaccountname' }}">
                                    @error('ldap_username_field')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {!! $message !!}
                                            </span>
                                    @enderror

                                </div>
                            </div>

                            <!-- LDAP Last Name Field -->
                            <div class="form-group {{ $errors->has('ldap_lname_field') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_lname_field">{{ trans('admin/settings/general.ldap_lname_field') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="ldap_lname_field" id="ldap_lname_field" value="{{  old('ldap_lname_field', $setting->ldap_lname_field) }}" class="form-control" placeholder="{{  trans('general.example') .'sn' }}">
                                    @error('ldap_lname_field')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                </div>
                            </div>

                            <!-- LDAP First Name field -->
                            <div class="form-group {{ $errors->has('ldap_fname_field') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_fname_field">{{ trans('admin/settings/general.ldap_fname_field') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="ldap_fname_field" id="ldap_fname_field" value="{{  old('ldap_fname_field', $setting->ldap_fname_field) }}" class="form-control" placeholder="{{ trans('general.example') .'givenname'  }}">
                                    @error('ldap_fname_field')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                </div>
                            </div>

                            <!-- LDAP Display Name Field -->
                            <div class="form-group {{ $errors->has('ldap_display_name') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_lname_field">{{ trans('admin/settings/general.ldap_display_name') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="ldap_display_name" id="ldap_display_name" value="{{  old('ldap_display_name', $setting->ldap_display_name) }}" class="form-control" placeholder="{{  trans('general.example') .'displayname' }}">
                                    <p class="help-block">{{ trans('admin/settings/general.ldap_display_name_help') }}</p>
                                    @error('ldap_display_name')
                                    <span class="alert-msg">
                                                    <x-icon type="x" />
                                                    {{ $message }}
                                                </span>
                                    @enderror

                                </div>
                            </div>

                            <!-- LDAP emp number -->
                            <div class="form-group {{ $errors->has('ldap_emp_num') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_emp_num">{{ trans('admin/settings/general.ldap_emp_num') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'employeenumber/employeeid' }}" name="ldap_emp_num" type="text" id="ldap_emp_num" value="{{ old('ldap_emp_num', $setting->ldap_emp_num) }}">
                                    @error('ldap_emp_num')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <!-- LDAP department -->
                            <div class="form-group {{ $errors->has('ldap_dept') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_dept">{{ trans('admin/settings/general.ldap_dept') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'department' }}" name="ldap_dept" type="text" id="ldap_dept" value="{{ old('ldap_dept', $setting->ldap_dept) }}">

                                    @error('ldap_dept')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <!-- LDAP Manager -->
                            <div class="form-group {{ $errors->has('ldap_dept') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_manager">{{ trans('admin/settings/general.ldap_manager') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder=" {{ trans('general.example') .'manager' }}" name="ldap_manager" type="text" value="{{ old('ldap_manager', $setting->ldap_manager) }}">
                                    @error('ldap_manager')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP email -->
                            <div class="form-group {{ $errors->has('ldap_email') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_email">{{ trans('admin/settings/general.ldap_email') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'mail' }}" name="ldap_email" type="text" id="ldap_email" value="{{ old('ldap_email', $setting->ldap_email) }}">
                                    @error('ldap_email')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP Phone -->
                            <div class="form-group {{ $errors->has('ldap_phone') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_phone">{{ trans('admin/settings/general.ldap_phone') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'telephonenumber' }}" name="ldap_phone" type="text" id="ldap_phone" value="{{ old('ldap_phone', $setting->ldap_phone_field) }}">
                                    @error('ldap_phone')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP Mobile -->
                            <div class="form-group {{ $errors->has('ldap_mobile') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_mobile">{{ trans('admin/settings/general.ldap_mobile') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'mobile' }}" name="ldap_mobile" type="text" id="ldap_mobile" value="{{ old('ldap_mobile', $setting->ldap_mobile) }}">
                                    @error('ldap_mobile')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- LDAP Job title -->
                            <div class="form-group {{ $errors->has('ldap_jobtitle') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_jobtitle">{{ trans('admin/settings/general.ldap_jobtitle') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'title' }}" name="ldap_jobtitle" type="text" id="ldap_jobtitle" value="{{ old('ldap_jobtitle', $setting->ldap_jobtitle) }}">
                                    @error('ldap_jobtitle')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP address -->
                            <div class="form-group {{ $errors->has('ldap_address') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_address">{{ trans('admin/settings/general.ldap_address') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" name="ldap_address" placeholder="{{ trans('general.example') .'streetaddress' }}"  type="text" id="ldap_address" value="{{ old('ldap_address', $setting->ldap_address) }}">
                                    @error('ldap_address')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- LDAP city -->
                            <div class="form-group {{ $errors->has('ldap_city') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_city">{{ trans('admin/settings/general.ldap_city') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'l' }}" name="ldap_city" type="text" id="ldap_city" value="{{ old('ldap_city', $setting->ldap_city) }}">
                                    @error('ldap_city')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- LDAP state -->
                            <div class="form-group {{ $errors->has('ldap_state') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_state">{{ trans('admin/settings/general.ldap_state') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'st' }}"  name="ldap_state" type="text" id="ldap_state" value="{{ old('ldap_state', $setting->ldap_state) }}">
                                    @error('ldap_state')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- LDAP zip -->
                            <div class="form-group {{ $errors->has('ldap_zip') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_zip">{{ trans('admin/settings/general.ldap_zip') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" name="ldap_zip" type="text" id="ldap_zip" placeholder="{{ trans('general.example') .'postalcode' }}"  value="{{ old('ldap_zip', $setting->ldap_zip) }}">
                                    @error('ldap_zip')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror
                                </div>
                            </div>


                            <!-- LDAP Country -->
                            <div class="form-group {{ $errors->has('ldap_country') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_country">{{ trans('admin/settings/general.ldap_country') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'co' }}" name="ldap_country" type="text" id="ldap_country" value="{{ old('ldap_country', $setting->ldap_country) }}">
                                    @error('ldap_country')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP Location -->
                            <div class="form-group {{ $errors->has('ldap_location') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_location">{{ trans('admin/settings/general.ldap_location') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" placeholder="{{ trans('general.example') .'physicaldeliveryofficename' }}" name="ldap_location" type="text" id="ldap_location" value="{{ old('ldap_location', $setting->ldap_location) }}">
                                    <p class="help-block">{!! trans('admin/settings/general.ldap_location_help') !!}</p>
                                    @error('ldap_location')
                                    <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP active flag -->
                            <div class="form-group {{ $errors->has('ldap_active_flag') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_active_flag">{{ trans('admin/settings/general.ldap_active_flag') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="ldap_active_flag" id="ldap_active_flag" value="{{  old('ldap_active_flag', $setting->ldap_active_flag) }}" class="form-control">
                                    <p class="help-block">{!! trans('admin/settings/general.ldap_activated_flag_help') !!}</p>

                                    @error('ldap_active_flag')
                                    <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {{ trans('general.feature_disabled') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- LDAP invert active flag -->
                            <div class="form-group">
                                <div class="col-md-3 text-right">
                                    <label for="ldap_invert_active_flag">
                                        {{ trans('admin/settings/general.ldap_invert_active_flag') }}
                                    </label>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-control">
                                        <input type="checkbox" name="ldap_invert_active_flag" value="1" id="ldap_invert_active_flag" @checked(old('ldap_invert_active_flag', $setting->ldap_invert_active_flag)) />
                                        {{ trans('general.yes') }}
                                    </label>
                                    @error('ldap_invert_active_flag')
                                    <span class="alert-msg">
                                                 <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                    @enderror

                                    <p class="help-block">
                                        {!! trans('admin/settings/general.ldap_invert_active_flag_help') !!}
                                    </p>

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning">
                                            <x-icon type="locked" />
                                            {!! trans('general.feature_disabled') !!}
                                        </p>
                                    @endif
                                </div>

                            </div>
                        </fieldset>

                        <fieldset id="ldap_test_settings">
                            <x-form-legend
                               help_text="{{ trans('admin/settings/general.save_ldap_first') }}">
                                        {{ trans('admin/settings/general.legends.test') }}
                            </x-form-legend>
                            @if ($setting->ldap_enabled)

                                <!-- LDAP Login test -->
                                <div class="form-group">
                                    <div class="col-md-3 text-right">
                                        <label for="test_ldap_login"> {{trans('admin/settings/general.ldap_test_login')}} </label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" name="ldaptest_user" id="ldaptest_user"  class="form-control" placeholder="{{trans('admin/settings/general.ldap_username_placeholder')}}">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="password" name="ldaptest_password" id="ldaptest_password" class="form-control" placeholder="{{trans('admin/settings/general.ldap_password_placeholder')}}" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                                            </div>
                                            <div class="col-md-3 text-right">
                                                <a class="btn btn-default btn-sm" id="ldaptestlogin" style="margin-right: 10px;">{{ trans('admin/settings/general.ldap_test') }}</a>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="col-md-8 col-md-offset-3">
                                        <span id="ldaptestloginicon"></span>
                                        <span id="ldaptestloginresult"></span>
                                        <span id="ldaptestloginstatus"></span>
                                    </div>
                                    <div class="col-md-8 col-md-offset-3">
                                        <p class="help-block">{{ trans('admin/settings/general.ldap_login_test_help') }}</p>
                                    </div>

                                </div>

                                <!-- LDAP test -->
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-3" id="ldaptestrow">
                                        <a class="btn btn-default btn-sm" id="ldaptest" style="margin-right: 10px;">{{ trans('admin/settings/general.ldap_test_sync') }}</a>
                                        <p class="help-block">{{ trans('admin/settings/general.ldap_login_sync_help') }}</p>
                                    </div>
                                    <div class="col-md-12">
                                        <br />
                                        <div id="ldapad_test_results" class="hidden well well-sm"></div>
                                    </div>
                                    <div class="col-md-8 col-md-offset-3">
                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                            @endif

                        </fieldset>


                        <fieldset>
                            <x-form-legend>
                                {{ trans('admin/settings/general.legends.misc') }}
                            </x-form-legend>

                                <!-- LDAP Forgotten password -->
                                <div class="form-group {{ $errors->has('custom_forgot_pass_url') ? 'error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="custom_forgot_pass_url">{{ trans('admin/settings/general.custom_forgot_pass_url') }}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control" placeholder="{{ trans('general.example') .'https://my.ldapserver-forgotpass.com' }}" name="custom_forgot_pass_url" type="url" id="custom_forgot_pass_url" value="{{ old('custom_forgot_pass_url', $setting->custom_forgot_pass_url) }}">
                                        <p class="help-block">{{ trans('admin/settings/general.custom_forgot_pass_url_help') }}</p>
                                        @error('custom_forgot_pass_url')
                                            <span class="alert-msg">
                                                <x-icon type="x" />
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if (config('app.lock_passwords')===true)
                                            <p class="text-warning">
                                                <x-icon type="locked" />
                                                {{ trans('general.feature_disabled') }}
                                            </p>
                                        @endif
                                    </div>
                                </div><!-- LDAP Server -->

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


@endsection

@push('js')

    <script nonce="{{ csrf_token() }}">



        /**
         * Check to see if is_ad is checked, if not disable the ad_domain field
         */
        $(function() {

            // If the app is locked, disable all fields except the top search fields
            @if (config('app.lock_passwords') === true)
                $("input").prop('disabled', 'disabled');
                $("textarea").prop('disabled', 'disabled');
                $("button").prop('disabled', 'disabled');
                $("#tagSearch").removeAttr("disabled");
                $("#search").removeAttr("disabled");
                $("#topSearchButton").removeAttr("disabled");
            @endif



            if ($('#is_ad').prop('checked') === false) {
                $('#ad_domain').prop('disabled', 'disabled');
                $("#ad_domain").prop('required',false);
            }


            // Mark fields as required if LDAP is enabled
            if ($('#ldap_enabled').prop('checked') === false) {
                $("#ldap_server").prop('required',false);
                $("#ldap_auth_filter_query").prop('required',false);
                $("#ldap_filter").prop('required',false);
                $("#ldap_username_field").prop('required',false);
                $("#ldap_uname").prop('required',false);
                $("#ldap_pword").prop('required',false);
                $("#ldap_basedn").prop('required',false);
                $("#ldap_fname_field").prop('required',false);
            }

            $("#ldap_enabled").change(function() {

                if (this.checked) {
                    $("#ldap_server").prop('required',true);
                    $("#ldap_auth_filter_query").prop('required',true);
                    $("#ldap_filter").prop('required',true);
                    $("#ldap_uname").prop('required',true);
                    $("#ldap_username_field").prop('required',true);
                    $("#ldap_pword").prop('required',true);
                    $("#ldap_basedn").prop('required',true);
                } else {
                    $("#ldap_server").prop('required',false);
                    $("#ldap_auth_filter_query").prop('required',false);
                    $("#ldap_filter").prop('required',false);
                    $("#ldap_username_field").prop('required',false);
                    $("#ldap_pword").prop('required',false);
                    $("#ldap_basedn").prop('required',false);
                    $("#ldap_fname_field").prop('required',false);
                }

            });
        });

        $("#is_ad").change(function() {
            $('#ad_domain').prop('disabled', 'disabled');

            if (this.checked) {
                $('#ad_domain').toggleDisabled();
            }

        });

        /**
         * Toggle the server info based on the is_ad checkbox
         */
        $('#is_ad').on('ifClicked', function(){
            $('#ad_domain').toggleDisabled();
            //$('#ldap_server').toggleDisabled();
        });


        /**
         * Test the LDAP connection settings
         */
        $("#ldaptest").click(function () {
            $("#ldapad_test_results").removeClass('hidden text-success text-danger');
            $("#ldapad_test_results").html('');
            $("#ldapad_test_results").html('<x-icon type="spinner" /> {{ trans('admin/settings/message.ldap.testing') }}');
            $.ajax({
                url: '{{ route('api.settings.ldaptest') }}',
                type: 'GET',
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: {},
                dataType: 'json',

                success: function (data) {
                    $("#ldapad_test_results").html('');
                    let html = buildLdapTestResults(data)
                    $("#ldapad_test_results").html(
                        html
                    );
                },

                error: function (data) {
                    $("#ldapad_test_results").html('');
                    $("#ldapad_test_results").addClass('text-danger');
                    let errorIcon = '<i class="fas fa-exclamation-triangle text-danger"></i>' + ' ';
                    if (data.status == 500) {
                        $('#ldapad_test_results').html(errorIcon + '{{ trans('admin/settings/message.ldap.500') }}');
                    } else if (data.status == 400) {
                        let errorMessage = '';
                        if( typeof data.responseJSON.user_sync !== 'undefined') {
                            errorMessage =  data.responseJSON.user_sync.message;
                        }
                        if( typeof data.responseJSON.message !== 'undefined') {
                            errorMessage =  data.responseJSON.message;
                        }
                        $('#ldapad_test_results').html(errorIcon + errorMessage);
                    } else {
                        $('#ldapad_test_results').html('{{ trans('admin/settings/message.ldap.error') }}');
                       // $('#ldapad_test_results').html(errorIcon + data.responseText.message);
                    }
                }


            });
        });

        /**
         * Build the results html table
         */
        function buildLdapTestResults(results) {
            let html = '<ul style="list-style: none;padding-left: 5px;">'
            html += '<li class="text-success"><i class="fas fa-check""></i> ' + results.login.message + ' </li>'
            html += '<li class="text-success"><i class="fas fa-check""></i> ' + results.bind.message + ' </li>'
            html += '</ul>'
            html += '<div style="overflow:auto;">'
            html += '<div>{{ trans('admin/settings/message.ldap.sync_success') }}<br><br></div>'
            html += '<div class="table-wrapper"><table class="table table-bordered table-condensed">'
            html += buildLdapResultsTableHeader()
            html += buildLdapResultsTableBody(results.user_sync.users)
            html += '</table></div>'
            html += '</div>'
            return html;
        }

        function buildLdapResultsTableHeader(user)
        {
            var keys = [
                '{{ trans('admin/settings/general.employee_number') }}',
                '{{ trans('mail.username') }}',
                '{{ trans('admin/users/table.display_name') }}',
                '{{ trans('general.first_name') }}',
                '{{ trans('general.last_name') }}',
                '{{ trans('general.email') }}',
                '{{ trans('general.phone') }}',
                '{{ trans('admin/users/table.mobile') }}',
                '{{ trans('admin/users/table.manager') }}',
                '{{ trans('general.address') }}',
                '{{ trans('general.city') }}',
                '{{ trans('general.state') }}',
                '{{ trans('general.zip') }}',
                '{{ trans('general.country') }}',
                '{{ trans('general.location') }}',
            ]
            let header = '<thead><tr>'
            for (var i in keys) {
                header += '<th style="white-space: nowrap;">' + keys[i] + '</th>'
            }
            header += "</tr></thead>"
            return header;
        }

        function buildLdapResultsTableBody(users)
        {
            let body = '<tbody>'
            for (var i in users) {
                body += '<tr>';
                body += '<td style="white-space: nowrap;">' + (users[i].employee_number ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].username ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].display_name ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].firstname ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].lastname ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].email ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].phone ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].mobile ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;"><span class="nullval">' + (users[i].manager ?? 'NULL') + '</span></td>';
                body += '<td style="white-space: nowrap;">' + (users[i].address ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].city ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].state ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].zip ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].country ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '<td style="white-space: nowrap;">' + (users[i].location ?? '<span class="nullval">NULL</span>') + '</td>';
                body += '</tr>'
            }
            body += "</tbody>"
            return body;
        }


        $("#ldaptestlogin").click(function(){
            $("#ldaptestloginrow").removeClass('text-success');
            $("#ldaptestloginrow").removeClass('text-danger');
            $("#ldaptestloginstatus").removeClass('text-danger');
            $("#ldaptestloginstatus").html('');
            $("#ldaptestloginicon").html('<x-icon type="spinner" /> {{ trans('admin/settings/message.ldap.testing_authentication') }}');
            $.ajax({
                url: '{{ route('api.settings.ldaptestlogin') }}',
                type: 'POST',
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'ldaptest_user': $('#ldaptest_user').val(),
                    'ldaptest_password': $('#ldaptest_password').val()
                },

                dataType: 'json',

                success: function (data) {
                    $("#ldaptestloginicon").html('');
                    $("#ldaptestloginrow").addClass('text-success');
                    $("#ldaptestloginstatus").addClass('text-success');
                    $("#ldaptestloginstatus").html('<i class="fas fa-check text-success"></i> {{ trans('admin/settings/message.ldap.authentication_success') }}');
                },

                error: function (data) {

                    if (data.responseJSON) {
                        var errors = data.responseJSON.message;
                    } else {
                        var errors;
                    }

                    var error_text = '';

                    $("#ldaptestloginicon").html('');
                    $("#ldaptestloginstatus").addClass('text-danger');
                    $("#ldaptestloginicon").html('');

                    if (data.status == 500) {
                        $('#ldaptestloginstatus').html('{{ trans('admin/settings/message.ldap.500') }}');
                    } else if (data.status == 400) {

                        if (typeof errors !='string') {

                            if (errors.length > 0) {
                                error_text += '<ul style="list-style: none; padding-left: 5px;">';
                                for (i = 0; i < errors.length; i++) {
                                    if (errors[i]) {
                                        error_text += '<li><i class="fas fa-exclamation-triangle text-danger"></i> Error: ' + errors[i] + '</li>';
                                    }
                                }
                                error_text += '</ul>';
                            }


                        } else {
                            error_text = errors;
                        }

                        $('#ldaptestloginstatus').html(error_text);

                    } else {
                        $('#ldaptestloginstatus').html(data.responseText.message);
                    }
                }

            });
        });
    </script>
@endpush
