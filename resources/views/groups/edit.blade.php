@extends('layouts/edit-form', [
    'createText' => trans('admin/groups/titles.create') ,
    'updateText' => trans('admin/groups/titles.update'),
    'item' => $group,
    'formAction' => ($group !== null && $group->id !== null) ? route('groups.update', ['group' => $group->id]) : route('groups.store'),
    'topSubmit' => 'true',
])
@section('content')



@parent
@stop

@section('inputFields')

<!-- Name -->
<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
    <label for="name" class="col-md-3 control-label">{{ trans('admin/groups/titles.group_name') }}</label>
    <div class="col-md-8 required">
        <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required />
        {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>

<div class="form-group{!! $errors->has('notes') ? ' has-error' : '' !!}">
    <label for="notes" class="col-md-3 control-label">{{ trans('general.notes') }}</label>
    <div class="col-md-8">
        <x-input.textarea
                name="notes"
                id="notes"
                :value="old('notes', $group->notes)"
                placeholder="{{ trans('general.placeholders.notes') }}"
                aria-label="notes"
                rows="2"
        />

        {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>


<fieldset>
    <x-form-legend icon="warning" help_text="{{ (isset($all_users_count) && ($all_users_count < config('app.max_unpaginated_records'))) ? trans('general.add_users_to_group_help') : trans('admin/settings/general.too_many_users_to_show', ['count'=> $all_users_count, 'max' => config('app.max_unpaginated_records')]) }}">
       {{ trans('general.add_users_to_group') }}
    </x-form-legend>

<!-- this is a temp fix for the select2 not working inside modals -->



    <div class="form-group">
        <div class="col-md-12">

        @if(($all_users_count ) && ($all_users_count < config('app.max_unpaginated_records')))

        <!-- This hidden input will store the selected user IDs as a comma-separated string to avoid max_input_vars and max_multipart_body_parts php.ini issues -->
        <input type="hidden" name="users_to_sync" id="hidden_ids_box" class="form-control" value="{{ ($associated_users && is_array($associated_users)) ? implode(",", $associated_users->pluck('id')->toArray()) :  '' }}"/>

        <div class="addremove-multiselect">
                <div class="col-lg-5 col-sm-5 col-xs-12">
                    <h4>{{ trans('general.available_users') }}</h4>
                    <select id="available-select" class="multiselect available form-control" size="8" multiple="multiple">
                        @foreach($unselected_users as $unselected_user)
                            <option value="{{ $unselected_user->id }}" role="option">
                                {{ $unselected_user->present()->fullName }} ({{ $unselected_user->username }})
                            </option>
                        @endforeach
                    </select>
                    <p class="help-block text-right">
                        <x-icon type="users" />
                        <span id="count_unselected_box">{{ count($unselected_users) }}</span>
                    </p>
                </div>

                <div class="multiselect-controls col-lg-2 col-sm-2 col-xs-12">
                    <br><br>
                    <button type="button" id="rightall" class="rightall btn btn-sm btn-block btn-default" data-tooltip="true" title="{{ trans('general.add_all_users_to_group') }}"><i class="fa-solid fa-angles-right"></i></button>
                    <button type="button" id="right" class="right btn btn-sm btn-block btn-default" data-tooltip="true" title="{{ trans('general.add_selected_users_to_group') }}"><i class="fa-solid fa-angle-right"></i></button>
                    <button type="button" id="left" class="left btn btn-block  btn-sm btn-default" data-tooltip="true" title="{{ trans('general.remove_selected_users_from_group') }}"><i class="fa-solid fa-angle-left"></i></button>
                    <button type="button" id="leftall" class="leftall btn  btn-sm btn-block btn-default" data-tooltip="true" title="{{ trans('general.remove_all_users_from_group') }}"><i class="fa-solid fa-angles-left"></i></button>
                </div>

                <div class="col-lg-5 col-sm-5 col-xs-12">
                    <h4>{{ trans('general.users_to_add_to_group') }}</h4>
                    <select id="selected-select" class="multiselect selected form-control" size="8" multiple="multiple">
                        @foreach($associated_users as $associated_user)
                            <option value="{{ $associated_user->id }}" aria-selected="true" selected="selected" role="option">
                                {{ $associated_user->present()->fullName }} ({{ $associated_user->username }})
                            </option>
                        @endforeach
                    </select>
                    <p class="help-block text-right">
                        <x-icon type="users" />
                        <span id="count_selected_box"> {{ count($associated_users) }}</span>
                    </p>

                </div>

        </div>

    </div>
</div>
</fieldset>
@endif


<div class="col-md-12">
    @include ('partials.forms.edit.permissions-base', ['use_inherit' => false])
</div>
@stop

