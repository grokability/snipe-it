@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.editprofile') }}
@stop

{{-- Account page content --}}
@section('content')


<x-container class="col-md-6 col-md-offset-3">
  <x-form route="{{ route('profile.update') }}">
    <x-box>

          <fieldset name="display-preferences">
              <x-form.legend>
                  {{ trans('admin/settings/general.legends.display') }}
              </x-form.legend>

              <x-form.row
                  :label="trans('general.language')"
                  name="locale"
                  input_div_class="col-md-6"
              >
                  <x-slot:input>
                      @if (! config('app.lock_passwords'))
                          <x-input.locale-select name="locale" :selected="old('locale', $user->locale)"/>
                      @else
                          <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
                      @endif
                  </x-slot:input>
              </x-form.row>

              <x-form.row
                  :label="trans('admin/settings/general.nav_link_color')"
                  :item="$user"
                  name="nav_link_color"
                  type="colorpicker"
                  default="#ffffff"
                  :help_text="trans('admin/settings/general.nav_link_color_help')"
              />

              <x-form.row
                  :label="trans('admin/settings/general.link_light_color')"
                  :item="$user"
                  name="link_light_color"
                  type="colorpicker"
                  :default="$link_light_color"
                  :help_text="trans('admin/settings/general.link_light_color_help')"
              />

              <x-form.row
                  :label="trans('admin/settings/general.link_dark_color')"
                  :item="$user"
                  name="link_dark_color"
                  type="colorpicker"
                  :default="$link_light_color"
                  :help_text="trans('admin/settings/general.link_dark_color_help')"
              />

              <x-form.row
                  :label="trans('general.light_dark')"
                  name="light_dark"
                  input_div_class="col-md-9"
                  :help_text="trans('general.system_default_help')"
              >
                  <x-slot:input>
                      <p class="form-control-static" style="padding-top: 7px;">
                          <a data-theme-toggle-clear class="btn btn-theme btn-sm" href="{{ route('profile') }}">
                              {{ trans('general.system_default') }}
                          </a>
                      </p>
                  </x-slot:input>
              </x-form.row>


              <x-form.checkbox-row
                  name="enable_sounds"
                  :label="trans('account/general.enable_sounds')"
                  :item="$user"
                  data-toggle="sound-test"
                  data-sound-url="{{ url('sounds/success.mp3') }}"
              />

              <x-form.checkbox-row
                  name="enable_confetti"
                  :label="trans('account/general.enable_confetti')"
                  :item="$user"
                  data-toggle="confetti-test"
              />




          </fieldset>

          @can('self.profile')

          <fieldset name="user-preferences">
              <x-form.legend>
                  {{ trans('admin/settings/general.legends.your_details') }}
              </x-form.legend>
                    <x-form.row
                        :label="trans('general.first_name')"
                        :item="$user"
                        name="first_name"
                    />

                    <x-form.row
                        :label="trans('general.last_name')"
                        :item="$user"
                        name="last_name"
                    />

                    @can('self.edit_location')
                        <x-input.location-select
                            :label="trans('general.location')"
                            name="location_id"
                            :selected="old('location_id', $user->location_id)"
                        />

                        @if ($snipeSettings->full_multiple_companies_support == '1' && $snipeSettings->scope_locations_fmcs == '1')
                            @cannot('superadmin')
                                <div class="col-md-8 col-md-offset-3">
                                    <x-form.help name="location_id_fmcs" icon="tip">{{ trans('general.fmcs_location_select_note') }}</x-form.help>
                                </div>
                            @endcannot
                        @endif
                    @endcan

                    <x-form.row
                        :label="trans('admin/users/table.phone')"
                        :item="$user"
                        name="phone"
                        type="tel"
                        input_icon="phone"
                        input_group_addon="left"
                    />

                    <x-form.row
                        :label="trans('general.website')"
                        :item="$user"
                        name="website"
                        type="url"
                        input_icon="link"
                        input_group_addon="left"
                        placeholder="https://example.com"
                    />


                    @if (($user->avatar) && ($user->avatar != ''))
                        <x-input.image-upload :item="$user" fieldname="avatar" :imagePath="app('users_upload_path')" />
                    @else
                        <x-form.row
                            :label="trans('general.gravatar_email').' (Private)'"
                            name="gravatar"
                            input_div_class="col-md-8"
                        >
                            <x-slot:input>
                                <input class="form-control" type="text" name="gravatar" id="gravatar" value="{{ old('gravatar', $user->gravatar) }}" />
                                <p style="padding-top: 3px;">
                                    <img src="//secure.gravatar.com/avatar/{{ md5(strtolower(trim($user->gravatar))) }}" width="30" height="30" alt="{{ $user->display_name }} avatar image">
                                    {!! trans('general.gravatar_url') !!}
                                </p>
                            </x-slot:input>
                        </x-form.row>
                    @endif




                    @if ($snipeSettings->two_factor_enabled == '1')
                        <x-form.checkbox-row
                            name="two_factor_optin"
                            :label="trans('admin/settings/general.two_factor_enabled_text')"
                            :item="$user"
                            :disabled="auth()->user()->cannot('self.two_factor')"
                        />
                        <div class="col-md-8 col-md-offset-3">
                            @can('self.two_factor')
                                <x-form.help name="two_factor_optin">{{ trans('admin/settings/general.two_factor_enabled_warning') }}</x-form.help>
                            @else
                                <x-form.help name="two_factor_optin">{{ trans('admin/settings/general.two_factor_enabled_edit_not_allowed') }}</x-form.help>
                            @endcan
                            <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
                        </div>
                    @endif
          </fieldset>
          @endcan





    </x-box>
  </x-form>
</x-container>

@stop

