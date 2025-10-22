@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.saml_title') }}
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
    </style>


    <form method="POST" action="{{ route('settings.saml.save') }}" accept-charset="UTF-8" autocomplete="false" class="form-horizontal" role="form">
    <!-- CSRF Token -->
    {{csrf_field()}}

    <!-- this is a hack to prevent Chrome from trying to autocomplete fields -->
    <input type="text" name="prevent_autofill" id="prevent_autofill" value="" style="display:none;" />
    <input type="password" name="password_fake" id="password_fake" value="" style="display:none;" />



    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="saml"/>
                         {{ trans('admin/settings/general.saml') }}
                    </h2>
                </div>
                <div class="box-body">

                        <!-- Enable SAML -->
                        <div class="form-group{{ $errors->has('saml_integration') ? ' error' : '' }}">
                            <div class="col-md-3 text-right">
                                <strong>{{ trans('admin/settings/general.saml_integration') }}</strong>
                            </div>
                            <div class="col-md-9">

                                <label class="form-control{{ config('app.lock_passwords') === true ? ' form-control--disabled': '' }}">
                                    <input type="checkbox" name="saml_enabled" value="1" @checked(old('saml_enabled', $setting->saml_enabled)) @disabled(config('app.lock_passwords')) @class(['disabled' => config('app.lock_passwords')])/>
                                    {{ trans('admin/settings/general.saml_enabled') }}
                                </label>

                                {!! $errors->first('saml_integration', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                @if (config('app.lock_passwords') === true)
                                    <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                                @endif
                            </div>




                                @if ($setting->saml_enabled)
                                    <div class="col-md-9 col-md-offset-3">
                                    <!-- SAML SP Details -->
                                    <!-- SAML SP Entity ID -->
                                    <label for="saml_sp_entitiyid">{{ trans('admin/settings/general.saml_sp_entityid') }}</label>
                                    <input class="form-control" readonly="" name="saml_sp_entitiyid" type="text" value="{{ config('app.url') }}" id="saml_sp_entitiyid">
                                    <br>
                                    <!-- SAML SP ACS -->
                                    <label for="saml_sp_acs_url">{{ trans('admin/settings/general.saml_sp_acs_url') }}</label>
                                    <input class="form-control" readonly="" name="saml_sp_acs_url" type="text" value="{{ route('saml.acs') }}" id="saml_sp_acs_url">
                                    <br>
                                    <!-- SAML SP SLS -->
                                    <label for="saml_sp_sls_url">{{ trans('admin/settings/general.saml_sp_sls_url') }}</label>
                                    <input class="form-control" readonly="" name="saml_sp_sls_url" type="text" value="{{ route('saml.sls') }}" id="saml_sp_sls_url">
                                    <br>
                                    <!-- SAML SP Certificate -->
                                    @if (!empty($setting->saml_sp_x509cert))
                                         <label for="saml_sp_x509cert">{{ trans('admin/settings/general.saml_sp_x509cert') }}</label>
                                            <x-input.textarea
                                                name="saml_sp_x509cert"
                                                id="saml_sp_x509cert"
                                                :value="$setting->saml_sp_x509cert"
                                                wrap="off"
                                                readonly
                                            />
                                        <br>
                                    @endif
                                    <!-- SAML SP Metadata URL -->
                                    <label for="saml_sp_metadata_url">{{ trans('admin/settings/general.saml_sp_metadata_url') }}</label>
                                    <input class="form-control" readonly="" name="saml_sp_metadata_url" type="text" value="{{ route('saml.metadata') }}" id="saml_sp_metadata_url">
                                    <br>
                                    <p class="help-block">
                                        <a href="{{ route('saml.metadata') }}" target="_blank" class="btn btn-default" style="margin-right: 5px;">{{ trans('admin/settings/general.saml_download') }}</a>
                                    </p>
                                    </div>
                                @endif
                                {!! $errors->first('saml_enabled', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}

                        </div>


                        <!-- SAML IdP Metadata -->
                        <div class="form-group {{ $errors->has('saml_idp_metadata') ? 'error' : '' }}">
                        <div class="col-md-3 text-right">
                            <label for="saml_idp_metadata">{{ trans('admin/settings/general.saml_idp_metadata') }}</label>
                        </div>
                        <div class="col-md-9">
                            <x-input.textarea
                                name="saml_idp_metadata"
                                id="saml_idp_metadata"
                                :value="old('saml_idp_metadata', $setting->saml_idp_metadata)"
                                placeholder="https://example.com/idp/metadata"
                                wrap="off"
                            />
                            {!! $errors->first('saml_idp_metadata', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}<br>
                            <button type="button" class="btn btn-default" id="saml_idp_metadata_upload_btn" {{ $setting->demoMode }}>{{ trans('button.select_file') }}</button>
                            <input type="file" class="js-uploadFile" id="saml_idp_metadata_upload"
                                data-maxsize="{{ Helper::file_upload_max_size() }}"
                                accept="text/xml,application/xml" style="display:none; max-width: 90%" {{ $setting->demoMode }}>
                            
                            <p class="help-block">{{ trans('admin/settings/general.saml_idp_metadata_help') }}</p>
                        </div>
                        </div>

                        <!-- SAML Attribute Mapping Username -->
                        <div class="form-group {{ $errors->has('saml_attr_mapping_username') ? 'error' : '' }}">
                            <div class="col-md-3 text-right">
                                <label for="saml_attr_mapping_username">{{ trans('admin/settings/general.saml_attr_mapping_username') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input class="form-control" name="saml_attr_mapping_username" type="text" id="saml_attr_mapping_username" value="{{ old('saml_attr_mapping_username', $setting->saml_attr_mapping_username) }}">
                                <p class="help-block">{{ trans('admin/settings/general.saml_attr_mapping_username_help') }}</p>
                                {!! $errors->first('saml_attr_mapping_username', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                        <!-- SAML Force Login -->
                        <div class="form-group">
                            <div class="col-md-3 text-right">
                                <strong>{{  trans('admin/settings/general.saml_forcelogin_label') }}</strong>
                            </div>
                            <div class="col-md-9">
                                <label class="form-control{{ config('app.lock_passwords') === true ? ' form-control--disabled': '' }}">
                                    <input type="checkbox" name="saml_forcelogin" value="1" @checked(old('saml_forcelogin', $setting->saml_forcelogin)) @disabled(config('app.lock_passwords')) @class(['disabled' => config('app.lock_passwords')]) />
                                    {{ trans('admin/settings/general.saml_forcelogin') }}
                                </label>
                                <p class="help-block">{{ trans('admin/settings/general.saml_forcelogin_help') }}</p>
                                <p class="help-block">{{ route('login', ['nosaml']) }}</p>
                                {!! $errors->first('saml_forcelogin', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                        <!-- SAML Single Log Out -->
                        <div class="form-group">
                            <div class="col-md-3 text-right">
                                <strong>{{ trans('admin/settings/general.saml_slo_label') }}</strong>
                            </div>
                            <div class="col-md-9">
                                <label class="form-control{{ config('app.lock_passwords') === true ? ' form-control--disabled': '' }}">
                                    <input type="checkbox" name="saml_slo" value="1" @checked(old('saml_slo', $setting->saml_slo)) @disabled(config('app.lock_passwords')) @class(['minimal', 'disabled' => config('app.lock_passwords')])/>
                                    {{ trans('admin/settings/general.saml_slo') }}
                                </label>
                                <p class="help-block">{{ trans('admin/settings/general.saml_slo_help') }}</p>
                                {!! $errors->first('saml_slo', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                        <!-- SAML Custom Options -->
                        <div class="form-group {{ $errors->has('saml_custom_settings') ? 'error' : '' }}">
                        <div class="col-md-3 text-right">
                            <label for="saml_custom_settings">{{ trans('admin/settings/general.saml_custom_settings') }}</label>
                        </div>
                        <div class="col-md-9">
                            <x-input.textarea
                                name="saml_custom_settings"
                                :value="old('saml_custom_settings', $setting->saml_custom_settings)"
                                placeholder="example.option=false&#13;&#10;sp_x509cert=file:///...&#13;&#10;sp_private_key=file:///"
                                wrap="off"
                            />
                            <p class="help-block">{{ trans('admin/settings/general.saml_custom_settings_help') }}</p>
                            {!! $errors->first('saml_custom_settings', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                        </div>
                    </div>

                </div> <!--/.box-body-->
                <div class="box-footer">
                    <div class="text-left col-md-6">
                        <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                    </div>
                    <div class="text-right col-md-6">
                        <button type="submit" class="btn btn-primary"{{ config('app.lock_passwords') === true ? ' disabled': '' }}><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                    </div>

                </div>
            </div> <!-- /box -->

        </div> <!-- /.col-md-8-->
    </div> <!-- /.row-->

    </form>


@stop

@push('js')
    <script nonce="{{ csrf_token() }}">

        $('#saml_idp_metadata_upload_btn').click(function() {
            $('#saml_idp_metadata_upload').click();
        });

        $('#saml_idp_metadata_upload').on('change', function () {
            var fr = new FileReader();

            fr.onload = function(e) {
                $('#saml_idp_metadata').val(e.target.result);
            } 

            fr.readAsText(this.files[0]);
        });

    </script>
@endpush


