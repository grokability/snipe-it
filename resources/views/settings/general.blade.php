@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.general_title') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <form method="POST" autocomplete="off" class="form-horizontal" role="form" id="create-form">
    <!-- CSRF Token -->
    {{csrf_field()}}

    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="general-settings"/>
                        {{ trans('admin/settings/general.general_settings') }}
                    </h2>
                </div>

               <div class="box-body">

                   <div class="col-md-12">

                       <fieldset>
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.scoping') }}
                           </x-form.legend>
                            <!-- Full Multiple Companies Support -->
                            <x-form.checkbox-row
                                name="full_multiple_companies_support"
                                :label="trans('admin/settings/general.full_multiple_companies_support_text')"
                                :item="$setting"
                                :help_text="trans('admin/settings/general.full_multiple_companies_support_help_text')"
                            />
                            <!-- /.form-group -->

                            <!-- Scope Locations with Full Multiple Companies Support -->
                            <div class="form-group {{ $errors->has('scope_locations_fmcs') ? 'error' : '' }}">
                                <div class="col-md-8 col-md-offset-3">
                                    <livewire:location-scope-check />
                                </div>
                            </div>
                            <!-- /.form-group -->

                            <!-- Null Company Is Floater -->
                            <x-form.checkbox-row
                                name="null_company_is_floater"
                                :label="trans('admin/settings/general.null_company_is_floater_text')"
                                :item="$setting"
                                :disabled="! $setting->full_multiple_companies_support"
                                :help_text="trans('admin/settings/general.null_company_is_floater_help_text')"
                            />
                            <!-- /.form-group -->

                       </fieldset>

                       <fieldset>
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.formats') }}
                           </x-form.legend>
                           <!-- Email domain -->
                           <div class="form-group {{ $errors->has('email_domain') ? 'error' : '' }}">

                               <label for="email_domain" class="col-md-3 control-label">{{ trans('general.email_domain') }}</label>

                               <div class="col-md-8">
                                   <input class="form-control" placeholder="example.com" name="email_domain" type="text" value="{{ old('email_domain', $setting->email_domain) }}" id="email_domain">
                                   <span class="help-block">{{ trans('general.email_domain_help')  }}</span>
                                   <x-form.error name="email_domain" />
                               </div>
                           </div>


                           <!-- Email format -->
                           <div class="form-group {{ $errors->has('email_format') ? 'error' : '' }}">

                               <label for="email_format" class="col-md-3 control-label">{{ trans('admin/settings/general.email_formats.email_format') }}</label>

                               <div class="col-md-8">
                                   <x-input.email-format-select
                                       name="email_format"
                                       :selected="old('email_format', $setting->email_format)"
                                       style="width: 100%"
                                       aria-label="email_format"
                                   />
                                   <x-form.error name="email_format" />
                               </div>
                           </div>

                           <!-- Username format -->
                           <div class="form-group {{ $errors->has('username_format') ? 'error' : '' }}">

                               <label for="username_format" class="col-md-3 control-label">{{ trans('admin/settings/general.username_formats.username_format') }}</label>

                               <div class="col-md-8">
                                   <x-input.username-select
                                       name="username_format"
                                       :selected="old('username_format', $setting->username_format)"
                                       style="width: 100%"
                                       aria-label="username_format"
                                   />
                                   <x-form.error name="username_format" />

                                   <p class="help-block">
                                       {{ trans('admin/settings/general.username_format_help') }}
                                   </p>
                               </div>
                           </div>

                       </fieldset>


                       <fieldset>
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.profiles') }}
                           </x-form.legend>
                           <!-- user profile edit checkbox -->
                           <div class="form-group">
                               <div class="col-md-8 col-md-offset-3">
                                   <label class="form-control">
                                       <input type="checkbox" value="1" name="profile_edit" {{ (old('profile_edit', $setting->profile_edit)) == '1' ? ' checked="checked"' : '' }} aria-label="profile_edit">
                                       {{ trans('admin/settings/general.profile_edit_help') }}
                                   </label>

                               </div>
                           </div>
                       </fieldset>

                       <fieldset>
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.eula') }}
                           </x-form.legend>

                           <!-- Require signature for acceptance -->
                           <x-form.checkbox-row
                               name="require_accept_signature"
                               :label="trans('admin/settings/general.require_accept_signature')"
                               :item="$setting"
                               :help_text="trans('admin/settings/general.require_accept_signature_help_text')"
                           />
                           <!-- /.form-group -->

                           <!-- Default EULA -->
                           <div class="form-group {{ $errors->has('default_eula_text') ? 'error' : '' }}">
                               <label for="default_eula_text" class="col-md-3 control-label">{{ trans('admin/settings/general.default_eula_text') }}</label>

                               <div class="col-md-8">
                                   <x-input.textarea
                                           name="default_eula_text"
                                           :value="old('default_eula_text', $setting->default_eula_text)"
                                           placeholder="{{ trans('admin/settings/general.default_eula_text_placeholder') }}"
                                   />
                                   <x-form.error name="default_eula_text" />
                                   <p class="help-block">{{ trans('admin/settings/general.default_eula_help_text') }}</p>
                                   <x-form.help name="default_eula_text" icon="markdown">
                                       {{ trans('general.markdown') }}
                                   </x-form.help>
                               </div>
                           </div>

                       </fieldset>

                       <fieldset>
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.misc_display') }}
                           </x-form.legend>

                           <!-- Thumb Size -->
                           <div class="form-group {{ $errors->has('thumbnail_max_h') ? 'error' : '' }}">

                               <label for="thumbnail_max_h" class="col-md-3 control-label">{{ trans('admin/settings/general.thumbnail_max_h') }}</label>

                               <div class="col-md-8">
                                   <input class="form-control" style="max-width: 100px;" placeholder="50" maxlength="3" name="thumbnail_max_h" type="number" value="{{ old('thumbnail_max_h', ($setting->thumbnail_max_h ?? '25')) }}" id="thumbnail_max_h">
                                   <p class="help-block">{{ trans('admin/settings/general.thumbnail_max_h_help') }}</p>
                                   <x-form.error name="thumbnail_max_h" />
                               </div>
                           </div>

                           <!-- Model List prefs -->
                           <x-form.checkbox-row
                               name="show_in_model_list"
                               :label="trans('admin/settings/general.show_in_model_list')"
                               :options="[
                                   'image' => trans('general.image'),
                                   'category' => trans('general.category'),
                                   'manufacturer' => trans('general.manufacturer'),
                                   'model_number' => trans('general.model_no'),
                               ]"
                               :selected="$snipeSettings->modellist_displays"
                           />


                           <!-- Shortcuts enable -->
                           <x-form.checkbox-row
                               name="shortcuts_enabled"
                               :label="trans('admin/settings/general.shortcuts_enabled')"
                               :item="$setting"
                               :help_text="trans('admin/settings/general.shortcuts_help_text')"
                           />


                           <!-- Archived in List -->
                           <x-form.checkbox-row
                               name="show_archived_in_list"
                               :label="trans('admin/settings/general.show_archived_in_list_text')"
                               :item="$setting"
                           />

                           <!-- Show assets assigned to user's assets -->
                           <div class="form-group {{ $errors->has('show_assigned_assets') ? 'error' : '' }}">
                               <div class="col-md-8 col-md-offset-3">
                                   <label class="form-control">
                                       <input type="checkbox" name="show_assigned_assets" value="1" @checked(old('show_assigned_assets', $setting->show_assigned_assets)) />
                                       {{ trans('admin/settings/general.show_assigned_assets') }}
                                   </label>
                                   <p class="help-block">{{ trans('admin/settings/general.show_assigned_assets_help') }}</p>
                                   <x-form.error name="show_assigned_assets" />
                               </div>
                           </div>

                       </fieldset>


                       <fieldset>
                           <x-form.legend>
                               {{ trans('general.email') }}
                           </x-form.legend>

                           <!-- Mail test -->
                           <div class="form-group">

                               <label for="login_note" class="col-md-3 control-label">{{trans('admin/settings/general.test_mail')}}</label>

                               <div class="col-md-8" id="mailtestrow">
                                   <a class="btn btn-default btn-sm pull-left{{ (config('mail.reply_to.address') == '') ? ' disabled': '' }}" id="mailtest" style="margin-right: 10px;">
                                       {{ trans('admin/settings/general.mail_test') }}</a>
                                   <span id="mailtesticon" role="status" aria-live="polite" aria-atomic="true"></span>
                                   <span id="mailtestresult"></span>
                                   <span id="mailteststatus" role="status" aria-live="polite" aria-atomic="true"></span>
                               </div>
                               <div class="col-md-8 col-md-offset-3">
                                   <div id="mailteststatus-error" class="text-danger" role="alert" aria-live="assertive" aria-atomic="true"></div>
                               </div>
                               <div class="col-md-8 col-md-offset-3">
                                   <div class="help-block">

                                       @if (config('mail.reply_to.address') == '')
                                           <p class="text-warning">
                                               <x-icon type="warning"/> {{ trans('admin/settings/general.mail_test_no_email') }}
                                           </p>
                                       @else
                                           <p>{{ trans('admin/settings/general.mail_test_help', array('replyto' => config('mail.reply_to.address'))) }}</p>
                                       @endif

                                   </div>
                               </div>

                           </div>


                           <!-- Load images in emails -->
                           <div class="form-group {{ $errors->has('show_images_in_email') ? 'error' : '' }}">
                               <div class="col-md-8 col-md-offset-3">
                                   <label class="form-control">
                                       <input type="checkbox" name="show_images_in_email" value="1" @checked(old('show_images_in_email', $setting->show_images_in_email)) />
                                       {{ trans('admin/settings/general.show_images_in_email') }}
                                       <x-form.error name="show_images_in_email" />
                                   </label>

                               </div>
                           </div>

                       </fieldset>


                       <fieldset name="checkin-preferences">
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.checkin') }}
                           </x-form.legend>

                           <!-- Require Notes on checkin/checkout checkbox -->
                               <div class="form-group">
                                   <div class="col-md-8 col-md-offset-3">
                                       <label class="form-control">
                                           <input type="checkbox" value="1" name="require_checkinout_notes" {{ (old('require_checkinout_notes', $setting->require_checkinout_notes)) == '1' ? ' checked="checked"' : '' }} aria-label="require_checkinout_notes">
                                           {{ trans('admin/settings/general.require_checkinout_notes') }}
                                       </label>
                                           <p class="help-block">{{ trans('admin/settings/general.require_checkinout_notes_help_text') }}</p>
                                   </div>
                               </div>


                       </fieldset>



                       <fieldset name="dashboard">
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.dashboard') }}
                           </x-form.legend>

                           <!-- login text -->
                           <div class="form-group {{ $errors->has('login_note') ? 'error' : '' }}">

                               <label for="login_note" class="col-md-3 control-label">{{ trans('admin/settings/general.login_note') }}</label>

                               <div class="col-md-8">
                                   @if (config('app.lock_passwords'))

                                       <textarea class="form-control disabled" name="login_note" placeholder="{{trans('admin/settings/general.login_note_placeholder')}}" rows="2" aria-label="login_note" readonly>{{ old('login_note', $setting->login_note) }}</textarea>
                                       <x-form.error name="login_note" />
                                       <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                                   @else
                                       <textarea class="form-control" name="login_note" aria-label="login_note" placeholder="{{trans('admin/settings/general.login_note_placeholder')}}" rows="2">{{ old('login_note', $setting->login_note) }}</textarea>
                                       <x-form.error name="login_note" />
                                   @endif
                                   <p class="help-block">{!!  trans('admin/settings/general.login_note_help') !!}</p>
                               </div>
                           </div>

                               <!-- dash chart -->
                               <div class="form-group {{ $errors->has('dash_chart_type') ? 'error' : '' }}">

                                   <label for="show_in_model_list" class="col-md-3 control-label">{{ trans('general.pie_chart_type') }}</label>

                                   <div class="col-md-8">
                                       <x-input.select
                                           name="dash_chart_type"
                                           :options="['name' => 'Status Label Name', 'type' => 'Status Label Type']"
                                           :selected="old('dash_chart_type', $setting->dash_chart_type)"
                                           style="width: 80%"
                                       />
                                   </div>
                               </div>

                               <!-- dashboard text -->
                               <div class="form-group {{ $errors->has('dashboard_message') ? 'error' : '' }}">

                                   <label for="dashboard_message" class="col-md-3 control-label">{{ trans('admin/settings/general.dashboard_message') }}</label>

                                   <div class="col-md-8">
                                       @if (config('app.lock_passwords'))

                                           <textarea class="form-control disabled" name="login_note" placeholder="{{ trans('admin/settings/general.login_note_placeholder') }}" rows="2" aria-label="dashboard_message" readonly>{{ old('dashboard_message', $setting->login_note) }}</textarea>
                                           <x-form.error name="dashboard_message" />
                                           <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                                       @else
                                           <textarea class="form-control" aria-label="dashboard_message" name="dashboard_message" rows="2">{{ old('login_note', $setting->dashboard_message) }}</textarea>
                                           <x-form.error name="dashboard_message" />
                                       @endif
                                       <p class="help-block">
                                           {{ trans('admin/settings/general.dashboard_message_help') }}
                                           <i class="fab fa-markdown" aria-hidden="true"></i> {!!  trans('general.github_markdown') !!}
                                       </p>
                                   </div>
                               </div>
                       </fieldset>


                       <fieldset>
                           <x-form.legend>
                               {{ trans('admin/settings/general.legends.misc') }}
                           </x-form.legend>

                           <!-- Privacy Policy Footer-->
                           <div class="form-group {{ $errors->has('privacy_policy_link') ? 'error' : '' }}">

                               <label for="privacy_policy_link" class="col-md-3 control-label">{{ trans('admin/settings/general.privacy_policy_link') }}</label>

                               <div class="col-md-8">

                                   @if (config('app.lock_passwords'))
                                       <input class="form-control disabled" disabled="disabled" name="privacy_policy_link" type="text" id="privacy_policy_link" value="{{ old('privacy_policy_link', $setting->privacy_policy_link) }}">
                                   @else
                                       <input class="form-control" name="privacy_policy_link" type="text" id="privacy_policy_link" value="{{ old('privacy_policy_link', $setting->privacy_policy_link) }}">

                                   @endif

                                   <span class="help-block">{{ trans('admin/settings/general.privacy_policy_link_help')  }}</span>
                                   <x-form.error name="privacy_policy_link" />

                                   @if (config('app.lock_passwords')===true)
                                       <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                                   @endif

                               </div>
                           </div>
                           
                               <!-- Depreciation method -->
                               <div class="form-group {{ $errors->has('depreciation_method') ? 'error' : '' }}">

                                   <label for="depreciation_method" class="col-md-3 control-label">{{ trans('admin/depreciations/general.depreciation_method') }}</label>

                                   <div class="col-md-8">
                                       <x-input.select
                                           name="depreciation_method"
                                           id="depreciation_method"
                                           :options="['default' => trans('admin/depreciations/general.linear_depreciation'), 'diminish' => trans('admin/depreciations/general.diminishing_depreciation'), 'half_1' => trans('admin/depreciations/general.half_1'), 'half_2' => trans('admin/depreciations/general.half_2')]"
                                           :selected="old('depreciation_method', $setting->depreciation_method)"
                                           style="width: 80%"
                                       />
                                   </div>
                               </div>
                               <!-- /.form-group -->

                               <!-- unique serial -->
                               <div class="form-group">
                                   <div class="col-md-8 col-md-offset-3">
                                       <label class="form-control">
                                           <input type="checkbox" name="unique_serial" value="1" @checked(old('unique_serial', $setting->unique_serial)) />
                                           {{ trans('admin/settings/general.unique_serial') }}
                                           <x-form.error name="unique_serial" />
                                       </label>

                                       <p class="help-block">
                                           {{ trans('admin/settings/general.unique_serial_help_text') }}
                                       </p>
                                   </div>
                               </div>

                           <!-- Manager View -->
                           <div class="form-group {{ $errors->has('manager_view_enabled') ? 'error' : '' }}">

                               <div class="col-md-8 col-md-offset-3">
                                   <label class="form-control">
                                       <input type="checkbox" value="1" name="manager_view_enabled" {{ (old('manager_view_enabled', $setting->manager_view_enabled)) == '1' ? ' checked="checked"' : '' }} aria-label="manager_view_enabled">
                                       {{ trans('admin/settings/general.manager_view_enabled_text') }}
                                   </label>
                                   <p class="help-block">
                                       {{ trans('admin/settings/general.manager_view_enabled_help') }}
                                   </p>
                                   <x-form.error name="manager_view_enabled" />
                               </div>
                           </div>
                           <!-- /.form-group -->

                       </fieldset>


                   </div> <!--/.box-body-->
            </div> <!--/.box-body-->
            <div class="box-footer">
                <div class="text-left col-md-6">
                    <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                </div>
                <div class="text-right col-md-6">
                    <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                </div>

            </div>
            </div>

        </div> <!-- /box -->
    </div> <!-- /.col-md-8-->


    </form>

@stop

@section('moar_scripts')
    <!-- bootstrap color picker -->
    <script nonce="{{ csrf_token() }}">
        //color picker with addon
        $(".header-color").colorpicker();
        // toggle the disabled state of asset id prefix
        $('#auto_increment_assets').on('ifChecked', function(){
            $('#auto_increment_prefix').prop('disabled', false).focus();
        }).on('ifUnchecked', function(){
            $('#auto_increment_prefix').prop('disabled', true);
        });


        // Test Mail
        $("#mailtest").click(function(){
            $("#mailtestrow").removeClass('text-success');
            $("#mailtestrow").removeClass('text-danger');
            $("#mailtesticon").html('');
            $("#mailteststatus").html('');
            $('#mailteststatus-error').html('');
            $("#mailtesticon").html('<i class="fas fa-spinner spin"></i> {{ trans('admin/settings/message.mail.sending') }}');
            $.ajax({
                url: '{{ route('api.settings.mailtest') }}',
                type: 'POST',
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: {},
                dataType: 'json',

                success: function (data) {
                    console.dir(data);
                    $("#mailtesticon").html('');
                    $("#mailteststatus").html('');
                    $('#mailteststatus-error').html('');
                    $("#mailteststatus").removeClass('text-danger');
                    $("#mailteststatus").addClass('text-success');
                    if (data.message) {
                        $("#mailteststatus").html('<i class="fas fa-check text-success"></i> ' + data.message);
                    } else {
                        $("#mailteststatus").html('<i class="fas fa-check text-success"></i> {{ trans('admin/settings/message.mail.success') }}');
                    }
                },

                error: function (data) {

                    $("#mailtesticon").html('');
                    $("#mailteststatus").html('');
                    $('#mailteststatus-error').html('');
                    $("#mailteststatus").removeClass('text-success');
                    $("#mailteststatus").addClass('text-danger');
                    $("#mailtesticon").html('<i class="fas fa-exclamation-triangle text-danger"></i>');
                    $('#mailteststatus').html('{{ trans('admin/settings/message.mail.error') }}');
                    if (data.responseJSON) {
                        if (data.responseJSON.messages) {
                            $('#mailteststatus-error').html('Error: ' + data.responseJSON.messages);
                        } else {
                            $('#mailteststatus-error').html('{{ trans('admin/settings/message.mail.additional') }}');
                        }
                    } else {
                        console.dir(data);
                    }

                }


            });
        });

    </script>
@stop
