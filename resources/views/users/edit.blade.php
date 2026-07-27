@extends('layouts/default')
{{-- Page title --}}
@section('title')
	@if ($user->id)
		{{ trans('admin/users/table.updateuser') }}
		{{ $user->display_name }}
	@else
		{{ trans('admin/users/table.createuser') }}
	@endif

@parent
@stop


{{-- Page content --}}
@section('content')

    <x-container class="col-md-8 col-md-offset-2">
        {{-- novalidate: this form is wired to jQuery Validate (see the
             snipeValidatorOptions block in layouts/default.blade.php) which
             handles required, url, email, and complexity rules. Without
             novalidate the browser's HTML5 native validation fires FIRST on
             type=url / type=email fields and blocks submission with its own
             popup on the first invalid field, so jQuery Validate never gets
             a chance to walk the whole form and highlight every empty
             required field at once. --}}
        <x-form
            id="userForm"
            :item="$user"
            :route="isset($user->id) ? route('users.update', ['user' => $user->id]) : route('users.store')"
            novalidate
        >
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.nav-item name="info" class="active" :label="trans('general.information')"/>
                    <x-tabs.nav-item name="permissions" :label="trans('general.permissions')"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>
                    <x-tabs.pane name="info" class="active in">
                <!-- First Name -->
                        <x-form.row
                            :label="trans('general.first_name')"
                            name="first_name"
                            :item="$user"
                        />

                <!-- Last Name -->
                        <x-form.row
                            :label="trans('general.last_name')"
                            name="last_name"
                            :item="$user"
                        />

                <!-- Username -->
                        <x-form.row :label="trans('admin/users/table.username')" name="username" :item="$user">
                            <x-slot:input>
                                <input type="hidden" name="username" value="{{ old('username', $user->username) }}">
                                {{-- Editable only if the user isn't LDAP-managed, or this is a clone.
                                     LDAP-managed users get a locked notice + hidden field so validation still passes. --}}
                                @if ($user->ldap_import!='1' || str_contains(Route::currentRouteName(), 'clone'))
                                    {{-- Locked branch (demo mode or actor can't edit auth fields for this user)
                                         emits `disabled` and skips the readonly/onfocus autofill guard, since
                                         a disabled input can't be focused, typed into, or targeted by password
                                         managers anyway. Bootstrap's .form-control[disabled] handles the
                                         not-allowed cursor. --}}
                                    @if ((! Gate::allows('canEditAuthFields', $user)) || ((! Gate::allows('editableOnDemo')) && ($user->id)))
                                        <input class="form-control" type="text" name="username" id="username" value="{{ old('username', $user->username) }}" autocomplete="off" maxlength="191" disabled>
                                    @else
                                        <input class="form-control" type="text" name="username" id="username" value="{{ old('username', $user->username) }}" autocomplete="off" maxlength="191" {{ (Helper::checkIfRequired($user, 'username')) ? ' required' : '' }} onfocus="this.removeAttribute('readonly');" readonly>
                                    @endif
                                @else
                                    <x-form.help name="username-ldap-managed" icon="locked">
                                        {{ trans('general.managed_ldap') }}
                                    </x-form.help>
                                    <input type="hidden" name="username" value="{{ old('username', $user->username) }}">
                                @endif

                                @cannot('canEditAuthFields', $user)
                                    <x-form.help name="username-permission" icon="locked">
                                        {{ trans('general.action_permission_generic', ['action' => trans('general.edit'), 'item_type' => trans('general.username')]) }}
                                    </x-form.help>
                                @endcannot

                                <x-demo-lock :item="$user"/>
                            </x-slot:input>
                        </x-form.row>

              <!-- Activation Status (Can the user login?) -->
              {{-- Rendered ABOVE the password fields so the activated checkbox
                   sits above the password inputs whose visibility it controls.
                   snipeit.js hides the password rows when this checkbox is
                   unchecked. Keeping the checkbox above avoids the layout
                   jump caused by rows above the toggle appearing/disappearing. --}}
                        @if (((!Gate::allows('editableOnDemo'))  && ($user->id)) || (!Gate::allows('canEditAuthFields', $user)) || ($user->id == auth()->user()->id))
                            {{-- Disabled branch: no label column, the checkbox row
                                 spans col-md-9 col-md-offset-3 and carries up to three
                                 distinct conditional help-blocks (higher-role edit gate,
                                 demo app-lock, own-account gate). Each has its own icon
                                 and wording. Used <x-form.row> with an empty label and
                                 a full input slot to keep the wrapper consistent. --}}
                            <x-form.row name="activated" input_div_class="col-md-9 col-md-offset-3">
                                <x-slot:input>
                                    <label class="form-control form-control--disabled">
                                        <input type="checkbox" value="1" name="activated" class="disabled" {{ (old('activated', $user->activated)) == '1' ? ' checked="checked"' : '' }} disabled aria-label="activated">
                                        {{ trans('admin/users/general.activated_help_text') }}
                                    </label>

                                    @cannot('canEditAuthFields', $user)
                                        <x-form.help name="activated-permission" icon="locked">
                                            {{ trans('general.action_permission_generic', ['action' => trans('general.edit'), 'item_type' => trans('general.login_status')]) }}
                                        </x-form.help>
                                    @endcannot

                                    <x-demo-lock :item="$user"/>

                                    @if ($user->id == auth()->user()->id)
                                        <x-form.help name="activated-self" icon="locked">
                                            {{ trans('admin/users/general.activated_disabled_help_text') }}
                                        </x-form.help>
                                    @endif
                                </x-slot:input>
                            </x-form.row>
                        @else
                            <x-form.checkbox-row
                                name="activated"
                                :label="trans('admin/users/general.activated_help_text')"
                                :item="$user"
                                :help_text="trans('admin/users/general.activated_password_required_help')"
                                help_icon="tip"
                            />
                        @endif

                <!-- Password -->
                {{-- Inline display style pre-hides the row when the user is
                     landing on the form with activated unchecked (typical for
                     new-user create). Avoids the FOUC that would happen if we
                     rendered the fields visible and let snipeit.js hide them
                     on document-ready. JS still toggles visibility on
                     subsequent changes to the activated checkbox. The wand
                     generator button sits in the row's after_input slot
                     (col-md-1 sibling of the input column) so it lines up
                     with the "new" button pattern from x-input.user-select
                     instead of being fused into the input-group. --}}
                        <x-form.row
                            :label="trans('admin/users/table.password')"
                            name="password"
                            :style="((old('activated') == '1') || ($user->activated == '1')) ? null : 'display: none;'"
                        >
                            {{-- Wand is available whenever the actor can edit auth fields on a
                                 non-LDAP record AND either they're creating a new user (demo
                                 mode still lets you spin up test accounts) OR they're editing
                                 outside demo mode. Editing an existing user in demo mode is the
                                 only combination that hides the wand. --}}
                            @if (Gate::allows('canEditAuthFields', $user) && $user->ldap_import != '1' && (! $user->id || Gate::allows('editableOnDemo')))
                                <x-slot:after_input>
                                    <a href="#" class="btn btn-sm btn-theme" id="genPassword" data-password-length="{{ $snipeSettings->pwd_secure_min + 9 }}" data-tooltip="true" title="{{ trans('admin/users/general.generate_password') }}">
                                        <i class="fa-solid fa-wand-magic-sparkles fa-fw"></i>
                                    </a>
                                </x-slot:after_input>
                            @endif
                            <x-slot:input>
                                @if ($user->ldap_import!='1' || str_contains(Route::currentRouteName(), 'clone'))
                                    <div class="input-group">
                                        @if ((! Gate::allows('canEditAuthFields', $user)) || ((! Gate::allows('editableOnDemo')) && ($user->id)))
                                            <input type="password" name="password" class="form-control form-control--disabled" id="password" value="" maxlength="500" autocomplete="off" disabled>
                                        @else
                                            <input type="password" name="password" class="form-control" id="password" value="" maxlength="500" autocomplete="off" onfocus="this.removeAttribute('readonly');" readonly {{ ((Helper::checkIfRequired($user, 'password')) && (! $user->id)) ? ' required' : '' }}>
                                        @endif
                                        <span class="input-group-addon">
                                            {{-- jQuery's multi-selector: this eye toggles the visibility of
                                                 both the password and the confirmation field in one click, so
                                                 the confirmation row doesn't need its own eye addon. --}}
                                            <i data-toggle="#password, #password_confirm" class="fa fa-fw fa-eye toggle-password" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.toggle_password_visibility') }}</span>
                                        </span>
                                    </div>
                                    <x-form.error name="password"/>
                        @else
                                    <p class="form-control-static">{{ trans('general.managed_ldap') }}</p>
                        @endif

                                @cannot('canEditAuthFields', $user)
                                    <x-form.help name="password-permission" icon="locked">
                                        {{ trans('general.action_permission_generic', ['action' => trans('general.edit'), 'item_type' => trans('general.password')]) }}
                                    </x-form.help>
                                @endcannot

                                <x-demo-lock :item="$user"/>
                            </x-slot:input>
                        </x-form.row>

                @if (($user->ldap_import!='1') || str_contains(Route::currentRouteName(), 'clone'))
                    <!-- Password Confirm -->
                            <x-form.row
                                :label="trans('admin/users/table.password_confirm')"
                                name="password_confirmation"
                                :style="((old('activated') == '1') || ($user->activated == '1')) ? null : 'display: none;'"
                            >
                                <x-slot:input>
                                    <div class="input-group">
                                        @if ((! Gate::allows('canEditAuthFields', $user)) || ((! Gate::allows('editableOnDemo')) && ($user->id)))
                                            <input type="password" name="password_confirmation" id="password_confirm" class="form-control form-control--disabled" value="" maxlength="500" autocomplete="off" aria-label="password_confirmation" disabled>
                                        @else
                                            <input type="password" name="password_confirmation" id="password_confirm" class="form-control" value="" maxlength="500" autocomplete="off" aria-label="password_confirmation" {{ (! $user->id) ? ' required' : '' }} onfocus="this.removeAttribute('readonly');" readonly>
                                        @endif
                                        <span class="input-group-addon">
                                            {{-- Shares the same multi-selector data-toggle as the password
                                                 field's eye so both eyes and both fields stay in sync — see
                                                 the .toggle-password handler in snipeit.js. --}}
                                            <i data-toggle="#password, #password_confirm" class="fa fa-fw fa-eye toggle-password" aria-hidden="true"></i>
                                            <span class="sr-only">{{ trans('general.toggle_password_visibility') }}</span>
                                        </span>
                                    </div>

                                    @cannot('canEditAuthFields', $user)
                                        <x-form.help name="password_confirmation-permission" icon="locked">
                                            {{ trans('general.action_permission_generic', ['action' => trans('general.edit'), 'item_type' => trans('general.password')]) }}
                                        </x-form.help>
                                    @endcannot

                                    <x-demo-lock :item="$user"/>
                                    <x-form.error name="password_confirmation"/>
                                </x-slot:input>
                            </x-form.row>
                @endif

                  <!-- Email -->
                        <x-form.row :label="trans('admin/users/table.email')" name="email" :item="$user">
                            <x-slot:input>
                                @if ((! Gate::allows('canEditAuthFields', $user)) || ((! Gate::allows('editableOnDemo')) && ($user->id)))
                                    <input class="form-control" type="email" name="email" id="email" maxlength="191" value="{{ old('email', $user->email) }}" autocomplete="off" disabled>
                                @else
                                    <input class="form-control" type="email" name="email" id="email" maxlength="191" value="{{ old('email', $user->email) }}" autocomplete="off" onfocus="this.removeAttribute('readonly');" readonly {{ (Helper::checkIfRequired($user, 'email')) ? ' required' : '' }} @if (! $user->id && ! config('app.lock_passwords')) data-toggles-checkbox="#send_welcome" @endif>
                                @endif

                                @cannot('canEditAuthFields', $user)
                                    <x-form.help name="email-permission" icon="locked">
                                        {{ trans('general.action_permission_generic', ['action' => trans('general.edit'), 'item_type' => trans('general.email')]) }}
                                    </x-form.help>
                                @endcannot

                                <x-demo-lock :item="$user"/>
                            </x-slot:input>
                        </x-form.row>

                        <!-- Send welcome email to user -->
                        {{-- Starts disabled. snipeit.js flips it enabled once the
                             #email input has more than 5 chars, via the
                             data-toggles-checkbox attribute we render on #email
                             below. When app.lock_passwords is on we don't render
                             that attribute, so the checkbox stays permanently
                             disabled. --}}
                        @if (!$user->id)
                            <x-form.checkbox-row
                                name="send_welcome"
                                :label="trans('general.send_welcome_email_to_users')"
                                :help_text="trans('general.send_welcome_email_help')"
                                id="email_user_row"
                                :disabled="true"
                            />
                        @endif

                  {{-- Avatar upload is hidden when editing an existing user in demo mode
                       (would otherwise let visitors overwrite arbitrary users' avatars).
                       Creation flow keeps it available so demo-mode operators can still
                       spin up new accounts with an image. --}}
                  @if (! $user->id || Gate::allows('editableOnDemo'))
                      <x-input.image-upload :item="$user" fieldname="avatar" :imagePath="app('users_upload_path')" :clonedModel="$cloned_model ?? null" />
                  @else
                      <x-form.row :label="trans('general.image_upload')" name="avatar">
                          <x-slot:input>
                              @if ($user->avatar)
                                  <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(app('users_upload_path').e($user->avatar)) }}" class="img-responsive" alt="" style="max-width: 300px;">
                              @endif
                              <x-demo-lock :item="$user"/>
                          </x-slot:input>
                      </x-form.row>
                  @endif


                  <!-- begin optional disclosure arrow stuff -->

                      <div class="col-md-12">

                      <fieldset>

                          <x-form.legend>
                              <h4 id="optional_user_details" class="remember-toggle">
                                  <x-icon type="caret-down" class="fa-fw" id="toggle-arrow-optional_user_details" />
                                  {{ trans('admin/hardware/form.optional_infos') }}
                              </h4>
                          </x-form.legend>

                          <div class="col-md-12 toggle-content-optional_user_details">

                              <!-- everything here should be what is considered optional -->
                              <br>

                              <!-- Display Name -->
                              {{-- Custom input slot because we need getRawOriginal
                                   here, not the computed accessor. The row wrapper
                                   (label + error) still comes from the component. --}}
                              <x-form.row
                                  :label="trans('admin/users/table.display_name')"
                                  name="display_name"
                                  :item="$user"
                              >
                                  <x-slot:input>
                                      <input
                                          class="form-control"
                                          type="text"
                                          maxlength="191"
                                          name="display_name"
                                          id="display_name"
                                          aria-label="display_name"
                                          value="{{ old('display_name', $user->getRawOriginal('display_name')) }}"
                                      />
                                  </x-slot:input>
                              </x-form.row>


                              <!-- Company -->
                              {{-- When the actor has the rights and the FMCS pivot
                                   to actually manage companies, we render the dropdown; otherwise
                                   the target's current companies (or "(none)") in read-only labels.
                                   Either way one or more help-blocks may follow. --}}
                              <x-form.row :label="trans('general.company')" name="company_ids" id="company_ids">
                                  <x-slot:input>
                                      @if ((Gate::allows('canEditAuthFields', $user)) && (\App\Models\Company::canManageUsersCompanies()))
                                          <select class="js-data-ajax" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}" name="company_ids[]" style="width: 100%" multiple='multiple'>
                                              {{-- selected reads from the company_user pivot only. The
                                                   legacy users.company_id scalar can lag behind (LDAP
                                                   sync, pre-observer rows, etc.) so we never fall
                                                   back to it. --}}
                                              @foreach (old('company_ids', $user->companies->pluck('id')->toArray()) as $selectedCompanyId)
                                                  <option value="{{ $selectedCompanyId }}" selected="selected" role="option" aria-selected="true">
                                                      {{ \App\Models\Company::find($selectedCompanyId)?->name }}
                                                  </option>
                                              @endforeach
                                          </select>

                                          @if ($snipeSettings->full_multiple_companies_support == '1')
                                              @cannot('superadmin')
                                                  <x-form.help name="company_ids-fmcs-note">
                                                      <x-icon type="tip" class="text-info"/> {{ trans('general.fmcs_company_select_note') }}
                                                  </x-form.help>
                                              @endcannot
                                              @can('superadmin')
                                                  <x-form.help name="company_ids-fmcs-super-note" icon="tip">
                                                      {{ trans('general.fmcs_company_select_superadmin_note') }}
                                                  </x-form.help>
                                              @endcan
                                          @endif
                                          @if (! auth()->user()->canGrantFloaterStatus())
                                              <x-form.help name="company_ids-floater-warning">
                                                  <x-icon type="warning" class="text-warning"/> {{ trans('admin/users/general.floater_mode_warning_help') }}
                                              </x-form.help>
                                          @endif
                                      @else
                                          <p class="form-control-static">
                                              @if ($user->companies->isNotEmpty())
                                                  @foreach ($user->companies as $company)
                                                      <span class="label label-light">{!! $company->present()->formattedNameLink !!}</span>
                                                  @endforeach
                                              @else
                                                  <em class="text-muted">{{ trans('admin/users/general.no_companies_assigned') }}</em>
                                              @endif
                                          </p>

                                          <x-form.help name="company_ids-cannot-edit" icon="tip">
                                              @if (! Gate::allows('canEditAuthFields', $user))
                                                  {{ trans('admin/users/general.cannot_edit_privileged_user_companies') }}
                                              @else
                                                  {{ trans('admin/users/general.cannot_manage_companies_without_membership') }}
                                              @endif
                                          </x-form.help>
                                      @endif
                                  </x-slot:input>
                              </x-form.row>


                              <!-- language -->
                              <x-form.row :label="trans('general.language')" name="locale" :item="$user">
                                  <x-slot:input>
                                      <x-input.locale-select name="locale" :selected="old('locale', $user->locale)" />
                                  </x-slot:input>
                              </x-form.row>

                              <!-- Employee Number -->
                              <x-form.row
                                  :label="trans('general.employee_number')"
                                  name="employee_num"
                                  :item="$user"
                              />


                              <!-- Jobtitle -->
                              <x-form.row
                                  :label="trans('admin/users/table.title')"
                                  name="jobtitle"
                                  :item="$user"
                              />


                              <!-- Manager -->
                              <x-input.user-select
                                  :label="trans('admin/users/table.manager')"
                                  name="manager_id"
                                  :selected="old('manager_id', $user->manager_id)"
                                  :excludeId="$user->id ?? null"
                              />

                              <!--  Department -->
                              <x-form.row :label="trans('general.department')" name="department_id" :item="$user">
                                  <x-slot:input>
                                      <select class="js-data-ajax" data-endpoint="departments" data-placeholder="{{ trans('general.select_department') }}" name="department_id" id="department_select" aria-label="department_id" style="width: 100%">
                                          @if ($department_id = old('department_id', $user->department_id))
                                              <option value="{{ $department_id }}" selected="selected" role="option" aria-selected="true">
                                                  {{ (\App\Models\Department::find($department_id))?->name }}
                                              </option>
                                          @endif
                                      </select>
                                  </x-slot:input>
                              </x-form.row>

                              <x-form.row
                                  :label="trans('general.start_date')"
                                  name="start_date"
                                  type="datepicker"
                                  :item="$user"
                              />

                              <x-form.row
                                  :label="trans('general.end_date')"
                                  name="end_date"
                                  type="datepicker"
                                  :item="$user"
                              />

                              <!-- VIP checkbox -->
                              <x-form.checkbox-row
                                  name="vip"
                                  :label="trans('admin/users/general.vip_label')"
                                  :item="$user"
                                  :help_text="trans('admin/users/general.vip_help')"
                              />

                              <!-- Auto assign checkbox -->
                              <x-form.checkbox-row
                                  name="autoassign_licenses"
                                  :label="trans('general.autoassign_licenses')"
                                  :item="$user"
                                  :help_text="trans('general.autoassign_licenses_help_long')"
                              />

                              <!-- remote checkbox -->
                              <x-form.checkbox-row
                                  name="remote"
                                  :label="trans('admin/users/general.remote_label')"
                                  :item="$user"
                                  :help_text="trans('admin/users/general.remote_help')"
                              />


                              <!-- Location -->
                              <x-input.location-select
                                  :label="trans('general.location')"
                                  name="location_id"
                                  :selected="old('location_id', $user->location_id)"
                              />

                              <!-- Phone -->
                              <x-form.row
                                  :label="trans('admin/users/table.phone')"
                                  name="phone"
                                  type="tel"
                                  input_icon="phone"
                                  input_group_addon="left"
                                  :item="$user"
                              />

                              <!-- Mobile -->
                              <x-form.row
                                  :label="trans('admin/users/table.mobile')"
                                  name="mobile"
                                  type="tel"
                                  input_icon="mobile"
                                  input_group_addon="left"
                                  :item="$user"
                              />

                              <!-- Website URL -->
                              <x-form.row
                                  :label="trans('general.website')"
                                  name="website"
                                  type="url"
                                  input_icon="link"
                                  input_group_addon="left"
                                  :item="$user"
                              />

                              <!-- Address -->
                              <x-form.row
                                  :label="trans('general.address')"
                                  name="address"
                                  :item="$user"
                              />

                              <!-- City -->
                              <x-form.row
                                  :label="trans('general.city')"
                                  name="city"
                                  :item="$user"
                              />

                              <!-- State -->
                              <x-form.row
                                  :label="trans('general.state')"
                                  name="state"
                                  :item="$user"
                              />

                              <!-- Country -->
                              <x-form.row
                                  :label="trans('general.country')"
                                  name="country"
                                  :item="$user"
                                  :help_text="trans('general.countries_manually_entered_help')"
                              >
                                  <x-slot:input>
                                      <x-input.country-select
                                          name="country"
                                          :selected="old('country', $user->country)"
                                      />
                                  </x-slot:input>
                              </x-form.row>

                              <!-- Zip -->
                              <x-form.row
                                  :label="trans('general.zip')"
                                  name="zip"
                                  :item="$user"
                                  :maxlength="10"
                                  input_div_class="col-md-3 text-right"
                              />

                              <!-- Notes -->
                              <x-form.row
                                  :label="trans('admin/users/table.notes')"
                                  name="notes"
                                  type="textarea"
                                  :item="$user"
                                  :rows="5"
                              />

                              @if ($snipeSettings->two_factor_enabled!='')
                                  @if ($snipeSettings->two_factor_enabled=='1')
                                      <x-form.checkbox-row
                                          name="two_factor_optin"
                                          :label="trans('admin/settings/general.two_factor')"
                                          :item="$user"
                                          :disabled="!Gate::allows('editableOnDemo')"
                                          :help_text="Gate::allows('editableOnDemo') ? trans('admin/users/general.two_factor_admin_optin_help') : null"
                                      />
                                  @endif

                                  {{-- Reset 2FA lives on the user detail page
                                       (resources/views/users/view.blade.php via
                                       #confirmTwoFactorResetModal) so operators
                                       go through a confirmation modal that
                                       posts to the users.two_factor_reset web
                                       route. The equivalent inline AJAX widget
                                       that used to live here was removed for
                                       redundancy and to eliminate a chunk of
                                       inline JS ahead of the Vite migration. --}}

                              @endif

                              <!-- Groups -->
                              <x-form.row :label="trans('general.groups')" name="groups">
                                  <x-slot:input>
                                      @if ($groups->count())
                                          @if ((!Gate::allows('editableOnDemo') || (!Auth::user()->isSuperUser())))
                                              @if (count($userGroups->keys()) > 0)
                                                  <ul>
                                                      @foreach ($groups as $id => $group)
                                                          {!! ($userGroups->keys()->contains($id) ? '<li>'.e($group).'</li>' : '') !!}
                                                      @endforeach
                                                  </ul>
                                              @endif
                                                  <x-form.help name="groups-locked" icon="locked">
                                                  {{ trans('admin/users/general.group_memberships_helpblock') }}
                                                  </x-form.help>
                                          @else
                                              <select
                                                  name="groups[]"
                                                  size="{{ ($groups->count() > 25) ? '25' : '10' }}"
                                                  aria-label="groups[]"
                                                  id="groups[]"
                                                  multiple="multiple"
                                                  class="form-control">
                                                  @foreach ($groups as $id => $group)
                                                      <option value="{{ $id }}"
                                                          {{ ($userGroups->keys()->contains($id) ? ' selected' : '') }}>
                                                          {{ $group }}
                                                      </option>
                                                  @endforeach
                                              </select>
                                              <x-form.help name="groups-notes">{{ trans('admin/users/table.groupnotes') }}</x-form.help>
                                          @endif
                                      @else
                                          <p>{{ trans('admin/users/table.nogroup') }} <code>{{ trans('admin/settings/general.admin_settings') }} <i class="fa fa-cogs"></i> > {{ trans('general.groups') }} <i class="fas fa-user-friends"></i></code> </p>
                                      @endif
                                  </x-slot:input>
                              </x-form.row>
                          </div>

                    </fieldset>
                      </div>

                    </x-tabs.pane>

                    <x-tabs.pane name="permissions">

                        <x-form.legend help_text="{{ trans('permissions.use_groups') }}"/>

              @if (auth()->user()->isAdmin() && !auth()->user()->isSuperUser())
                  <x-alert type="info" icon="info">
                      {{ trans('admin/users/general.superadmin_permission_warning') }}
                  </x-alert>
              @elseif (!auth()->user()->isAdmin() && !auth()->user()->isSuperUser() && auth()->id() === $user->id)
                  <x-alert type="danger" icon="warning">
                      {{ trans('admin/users/general.self_permission_warning') }}
                  </x-alert>
              @elseif (!auth()->user()->isAdmin() && !auth()->user()->isSuperUser() && auth()->id() !== $user->id)
                  <x-alert type="danger" icon="warning">
                      {{ trans('admin/users/general.admin_permission_warning') }}
                  </x-alert>
              @endif

              @if (auth()->user()->isSuperUser() || auth()->user()->isAdmin() || (auth()->id() !== $user->id && !$user->isSuperUser()))
                  <div class="col-md-12">
                      @include('partials.forms.edit.permissions-base', ['use_inherit' => true, 'groupPermissions' => $userPermissions])
                  </div>
              @endif

                    </x-tabs.pane>
                </x-slot:tabpanes>

                <x-slot:footer>
                    <x-redirect_submit_options
                        index_route="users.index"
                        :button_label="trans('general.save')"
                        :options="[
                        'back' => trans('admin/hardware/form.redirect_to_type',['type' => trans('general.previous_page')]),
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => 'users']),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
                    ]"
                    />
                </x-slot:footer>
            </x-tabs>
        </x-form>
    </x-container>
@stop

