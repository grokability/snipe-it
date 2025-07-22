@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_edit') }}
    @parent
@stop


@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-sm btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')

    <style>
        .radio {
            margin-left: -20px;
        }
    </style>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <p>{{ trans('admin/users/general.bulk_update_help') }}</p>

            <div class="callout callout-warning">
                <i class="fas fa-exclamation-triangle"></i> {{ trans('admin/users/general.bulk_update_warn', ['user_count' => count($users)]) }}
            </div>

            <form class="form-horizontal" method="post" action="{{ route('users/bulkeditsave') }}" autocomplete="off" role="form">
                {{ csrf_field() }}

                <div class="box box-default">
                    <div class="box-body">


                        <!--  Department -->
                        @include ('partials.forms.edit.department-select', ['translated_name' => trans('general.department'), 'fieldname' => 'department_id'])


                        <div class="form-group">
                            <div class=" col-md-9 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" name="null_department_id" value="1" />
                                    {{ trans_choice('general.set_users_field_to_null', count($users), ['field' => trans('general.department'), 'user_count' => count($users)]) }}
                                </label>
                            </div>
                        </div>


                        <!-- Location -->
                        @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])

                        <div class="form-group">
                            <div class=" col-md-9 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" name="null_location_id" value="1" />
                                    {{ trans_choice('general.set_users_field_to_null', count($users), ['field' => trans('general.location'), 'user_count' => count($users)]) }}
                                </label>
                            </div>
                        </div>


                        <!-- Company -->
                        @if (\App\Models\Company::canManageUsersCompanies())
                            @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.select_company'), 'fieldname' => 'company_id'])

                            <div class="form-group">
                                <div class=" col-md-9 col-md-offset-3">
                                    <label class="form-control">
                                        <input type="checkbox" name="null_company_id" value="1" />
                                        {{ trans_choice('general.set_users_field_to_null', count($users), ['field' => trans('general.company'), 'user_count' => count($users)]) }}
                                    </label>
                                </div>
                            </div>

                        @endif

                        <!-- Manager -->
                    @include ('partials.forms.edit.user-select', ['translated_name' => trans('admin/users/table.manager'), 'fieldname' => 'manager_id'])

                        <div class="form-group">
                            <div class=" col-md-9 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" name="null_manager_id" value="1" />
                                    {{ trans_choice('general.set_users_field_to_null', count($users), ['field' => trans('admin/users/table.manager'), 'user_count' => count($users)]) }}
                                </label>
                            </div>
                        </div>


                        <!-- Language -->
                        <div class="form-group {{ $errors->has('locale') ? 'has-error' : '' }}">
                            <label class="col-md-3 control-label" for="locale">{{ trans('general.language') }}</label>
                            <div class="col-md-8">
                                <x-input.locale-select name="locale" :selected="old('locale', '')"/>
                                {!! $errors->first('locale', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class=" col-md-9 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" name="null_locale" value="1" />
                                    {{ trans_choice('general.set_users_field_to_null', count($users), ['field' => trans('general.language'), 'user_count' => count($users)]) }}
                                </label>
                            </div>
                        </div>

                        <!-- City -->
                        <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                            <label class="col-md-3 control-label" for="city">{{ trans('general.city') }}</label>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="city" id="city" aria-label="city" />
                                {!! $errors->first('city', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                         <!-- remote -->
                         <div class="form-group">
                            <div class="col-sm-3 control-label">
                                {{ trans('admin/users/general.remote') }}
                            </div>
                            <div class="col-sm-9">

                                    <label for="no_change_remote" class="form-control">
                                        <input type="radio" name="remote" id="no_change_remote" value="" checked aria-label="no_change_remote">
                                        {{  trans('general.do_not_change') }}
                                    </label>
                                    <label for="remote" class="form-control">
                                        <input type="radio" name="remote" id="remote" value="1" aria-label="remote">
                                        {{ trans('admin/users/general.remote_label') }}
                                    </label>
                                    <label for="not_remote" class="form-control">
                                        <input type="radio" name="remote" id="not_remote" value="0" aria-label="not_remote">
                                        {{ trans('admin/users/general.not_remote_label') }}
                                    </label>


                            </div>
                        </div> <!--/form-group-->

                        <!-- ldap_sync -->
                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                {{ trans('general.user_managed_passwords') }}
                            </div>
                            <div class="col-sm-9">
                                    <label for="no_change_ldap_import" class="form-control">
                                        <input type="radio" name="ldap_import" id="no_change_ldap_import" value="" checked aria-label="no_change_ldap_import">
                                        {{  trans('general.do_not_change') }}
                                    </label>
                                    <label for="no_ldap_import" class="form-control">
                                        <input type="radio" name="ldap_import" id="no_ldap_import" value="0" aria-label="no_ldap_import">
                                        {{ trans('general.user_managed_passwords_allow') }}
                                    </label>
                                    <label for="ldap_import" class="form-control">
                                        <input type="radio" name="ldap_import" id="ldap_import" value="1" aria-label="ldap_import">
                                        {{ trans('general.user_managed_passwords_disallow') }}
                                    </label>
                            </div>
                        </div> <!--/form-group-->

                        <!-- activated -->
                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                {{ trans('general.autoassign_licenses') }}
                            </div>
                            <div class="col-sm-9">

                                <label for="no_change_autoassign_licenses" class="form-control">
                                    <input type="radio" name="autoassign_licenses" id="no_change_autoassign_licenses" value="" checked aria-label="no_change_autoassign_licenses">
                                    {{  trans('general.do_not_change') }}
                                </label>
                                <label for="autoassign_licenses" class="form-control">
                                    <input type="radio" name="autoassign_licenses" id="autoassign_licenses" value="1" aria-label="autoassign_licenses">
                                    {{  trans('general.autoassign_licenses_help')}}
                                </label>
                                <label for="dont_autoassign_licenses" class="form-control">
                                    <input type="radio" name="autoassign_licenses" id="dont_autoassign_licenses" value="0" aria-label="dont_autoassign_licenses">
                                    {{  trans('general.no_autoassign_licenses_help')}}
                                </label>

                            </div>
                        </div> <!--/form-group-->

                        <!-- activated -->
                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                {{ trans('general.login_enabled') }}
                            </div>
                            <div class="col-sm-9">

                                    <label for="no_change_activated" class="form-control">
                                        <input type="radio" name="activated" id="no_change_activated" value="" checked aria-label="no_change_activated">
                                        {{  trans('general.do_not_change') }}
                                    </label>
                                    <label for="activated" class="form-control">
                                        <input type="radio" name="activated" id="activated" value="1" aria-label="activated">
                                        {{  trans('admin/users/general.user_activated')}}
                                    </label>
                                    <label for="deactivated" class="form-control">
                                        <input type="radio" name="activated" id="deactivated" value="0" aria-label="deactivated">
                                        {{  trans('admin/users/general.user_deactivated')}}
                                    </label>

                            </div>
                        </div> <!--/form-group-->


                        <!--  Groups -->
                        <div class="form-group{{ $errors->has('groups') ? ' has-error' : '' }}">
                            <label class="col-md-3 control-label" for="groups"> {{ trans('general.groups') }}</label>
                            <div class="col-md-6">
                                @if ((config('app.lock_passwords') || (!Auth::user()->isSuperUser())))
                                    <span class="help-block">{{  trans('admin/users/general.group_memberships_helpblock') }}</p>
                                @else
                                    <div class="controls">
                                        <select name="groups[]" id="groups[]" multiple="multiple" class="form-control" aria-label="groups">
                                        <option value="">{{  trans('admin/users/general.remove_group_memberships') }} </option>

                                  @foreach ($groups as $id => $group)
                                        <option value="{{ $id }}">{{ $group }} </option>
                                    @endforeach
                        </select>

                        <span class="help-block">
                          {{ trans('admin/users/table.groupnotes') }}
                        </span>
                      </div> <!--/controls-->
                        @endif
                    </div> <!--/col-md-5-->
                    </div>


                        <!-- Start Date -->
                        <div class="form-group {{ $errors->has('start_date') ? ' has-error' : '' }}">
                            <label for="start_date" class="col-md-3 control-label">{{ trans('general.start_date') }}</label>
                            <div class="col-md-4">
                                <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd"  data-autoclose="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.start_date') }}" name="start_date" id="start_date" value="{{ old('start_date') }}">
                                    <span class="input-group-addon"><x-icon type="calendar" /></span>
                                </div>
                                {!! $errors->first('start_date', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                            </div>
                            <div class="col-md-5">
                                <label class="form-control">
                                    <input type="checkbox" name="null_start_date" value="1" />
                                    {{ trans_choice('general.set_to_null', count($users),['selection_count' => count($users)]) }}
                                </label>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="form-group {{ $errors->has('end_date') ? ' has-error' : '' }}">
                            <label for="end_date" class="col-md-3 control-label">{{ trans('general.end_date') }}</label>
                            <div class="col-md-4">
                                <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd"  data-autoclose="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.end_date') }}" name="end_date" id="end_date" value="{{ old('end_date') }}">
                                    <span class="input-group-addon"><x-icon type="calendar" /></span>
                                </div>
                                {!! $errors->first('end_date', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                            </div>
                            <div class="col-md-5">
                                <label class="form-control">
                                    <input type="checkbox" name="null_end_date" value="1" />
                                    {{ trans_choice('general.set_to_null', count($users),['selection_count' => count($users)]) }}
                                </label>
                            </div>
                        </div>


                        @foreach ($users as $user)
                            <input type="hidden" name="ids[{{ $user->id }}]" value="{{ $user->id }}">
                        @endforeach
                    </div> <!--/.box-body-->

                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>

                        <button type="submit" class="btn btn-success"{{ (config('app.lock_passwords') ? ' disabled' : '') }}>
                            <x-icon type="checkmark" />
                            {{ trans('general.update') }}
                        </button>

                    </div><!-- /.box-footer -->
                </div> <!--/.box.box-default-->
            </form>
        </div> <!--/.col-md-8-->
    </div>
@stop
