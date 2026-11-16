@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.saml_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
        <x-form :route="route('settings.saml.save')">

            <x-box>
                <x-slot:header>
                    <x-icon type="saml"/> {{ trans('admin/settings/general.saml') }}
                </x-slot:header>

                {{-- Single demo-mode banner at the top so users don't wonder why
                     changes silently fail. Renders nothing outside demo mode. --}}
                <x-demo-callout />

                {{-- Enable SAML --}}
                <x-form.checkbox-row
                    name="saml_enabled"
                    :label="trans('admin/settings/general.saml_enabled')"
                    :item="$setting"
                    :disabled="config('app.lock_passwords') === true"
                />

                @if ($setting->saml_enabled)
                    {{-- SAML SP Entity ID (readonly, derived from app URL) --}}
                    <x-form.row
                        :label="trans('admin/settings/general.saml_sp_entityid')"
                        name="saml_sp_entitiyid"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <input class="form-control" readonly name="saml_sp_entitiyid" type="text" value="{{ config('app.url') }}" id="saml_sp_entitiyid">
                        </x-slot:input>
                    </x-form.row>

                    {{-- SAML SP ACS URL (readonly) --}}
                    <x-form.row
                        :label="trans('admin/settings/general.saml_sp_acs_url')"
                        name="saml_sp_acs_url"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <input class="form-control" readonly name="saml_sp_acs_url" type="text" value="{{ route('saml.acs') }}" id="saml_sp_acs_url">
                        </x-slot:input>
                    </x-form.row>

                    {{-- SAML SP SLS URL (readonly) --}}
                    <x-form.row
                        :label="trans('admin/settings/general.saml_sp_sls_url')"
                        name="saml_sp_sls_url"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <input class="form-control" readonly name="saml_sp_sls_url" type="text" value="{{ route('saml.sls') }}" id="saml_sp_sls_url">
                        </x-slot:input>
                    </x-form.row>

                    {{-- SAML SP Certificate (readonly textarea; only shown after a cert is generated) --}}
                    @if (! empty($setting->saml_sp_x509cert))
                        <x-form.row
                            :label="trans('admin/settings/general.saml_sp_x509cert')"
                            name="saml_sp_x509cert"
                            input_div_class="col-md-8"
                        >
                            <x-slot:input>
                                <x-input.textarea
                                    name="saml_sp_x509cert"
                                    id="saml_sp_x509cert"
                                    rows="20"
                                    :value="$setting->saml_sp_x509cert"
                                    wrap="off"
                                    readonly
                                />
                            </x-slot:input>
                        </x-form.row>
                    @endif

                    {{-- SAML SP Metadata URL (readonly + download button) --}}
                    <x-form.row
                        :label="trans('admin/settings/general.saml_sp_metadata_url')"
                        name="saml_sp_metadata_url"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <input class="form-control" readonly name="saml_sp_metadata_url" type="text" value="{{ route('saml.metadata') }}" id="saml_sp_metadata_url">
                            <p class="help-block">
                                <a href="{{ route('saml.metadata') }}" target="_blank" class="btn btn-theme" style="margin-right: 5px;">{{ trans('admin/settings/general.saml_download') }}</a>
                            </p>
                        </x-slot:input>
                    </x-form.row>
                @endif

                {{-- SAML IdP Metadata: paste XML directly or upload from disk.
                     The file input is hidden; the visible button proxies its
                     click, and the FileReader in the @push('js') block below
                     reads the selected file into the textarea. --}}
                <x-form.row
                    :label="trans('admin/settings/general.saml_idp_metadata')"
                    name="saml_idp_metadata"
                    :help_text="trans('admin/settings/general.saml_idp_metadata_help')"
                    input_div_class="col-md-8"
                >
                    <x-slot:input>
                        <x-input.textarea
                            name="saml_idp_metadata"
                            id="saml_idp_metadata"
                            :value="old('saml_idp_metadata', $setting->saml_idp_metadata)"
                            placeholder="https://example.com/idp/metadata"
                            wrap="off"
                        />
                        <br>
                        <button type="button" class="btn btn-theme" id="saml_idp_metadata_upload_btn" {{ $setting->demoMode }}>{{ trans('button.select_file') }}</button>
                        <input type="file" class="js-uploadFile" id="saml_idp_metadata_upload"
                            @disabled(config('app.lock_passwords'))
                            data-maxsize="{{ Helper::file_upload_max_size() }}"
                            accept="text/xml,application/xml" style="display:none; max-width: 90%" {{ $setting->demoMode }}>
                    </x-slot:input>
                </x-form.row>

                {{-- SAML Attribute Mapping Username --}}
                <x-form.row
                    :label="trans('admin/settings/general.saml_attr_mapping_username')"
                    name="saml_attr_mapping_username"
                    type="text"
                    :item="$setting"
                    :help_text="trans('admin/settings/general.saml_attr_mapping_username_help')"
                    :disabled="config('app.lock_passwords')"
                    input_div_class="col-md-8"
                />

                {{-- SAML Force Login --}}
                <x-form.checkbox-row
                    name="saml_forcelogin"
                    :label="trans('admin/settings/general.saml_forcelogin')"
                    :help_text="trans('admin/settings/general.saml_forcelogin_help')"
                    :item="$setting"
                    :disabled="config('app.lock_passwords') === true"
                />

                {{-- SAML Single Log Out --}}
                <x-form.checkbox-row
                    name="saml_slo"
                    :label="trans('admin/settings/general.saml_slo')"
                    :help_text="trans('admin/settings/general.saml_slo_help')"
                    :item="$setting"
                    :disabled="config('app.lock_passwords') === true"
                />

                {{-- SAML Auto-Create Users --}}
                <x-form.checkbox-row
                    name="saml_auto_create_users"
                    :label="trans('admin/settings/general.saml_auto_create_users')"
                    :help_text="trans('admin/settings/general.saml_auto_create_users_help')"
                    :item="$setting"
                    :disabled="config('app.lock_passwords') === true"
                />

                {{-- SAML Custom Options (raw settings passed to the SAML lib) --}}
                <x-form.row
                    :label="trans('admin/settings/general.saml_custom_settings')"
                    name="saml_custom_settings"
                    type="textarea"
                    :item="$setting"
                    :help_text="trans('admin/settings/general.saml_custom_settings_help')"
                    placeholder="example.option=false&#13;&#10;sp_x509cert=file:///...&#13;&#10;sp_private_key=file:///"
                    input_div_class="col-md-8"
                    wrap="off"
                />

                {{-- Explicit footer preserves the demo-mode-disabled save state
                     that the default x-box.footer doesn't offer. --}}
                <x-slot:customfooter>
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <x-button.submit class="btn-theme" :disabled="config('app.lock_passwords') === true" />
                        </div>
                    </div>
                </x-slot:customfooter>
            </x-box>

        </x-form>
    </x-container>

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
