@extends('layouts/edit-form', [
    'createText' => trans('admin/groups/titles.create') ,
    'updateText' => trans('admin/groups/titles.update'),
    'item' => $group,
    'formAction' => ($group !== null && $group->id !== null) ? route('groups.update', ['group' => $group->id]) : route('groups.store'),
    'container_classes' => 'col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0',
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


<div class="form-group{{ $errors->has('associated_users') ? ' has-error' : '' }}">

    <label for="associated_users[]" class="col-md-3 control-label">
        {{ trans('general.users') }}
    </label>

    <div class="col-md-7">
        <select class="js-data-ajax"
                data-endpoint="users"
                data-placeholder="{{ trans('general.select_user') }}"
                name="associated_users[]"
                style="width: 100%"
                id="associated_users[]"
                aria-label="associated_users[]"  multiple>

                <option value=""  role="option">{{ trans('general.select_user') }}</option>
                @if(isset($associated_users))
                    @foreach($associated_users as $associated_user)
                        <option value="{{ $associated_user->id }}" selected="selected" role="option" aria-selected="true"
                                role="option">
                            {{ $associated_user->present()->fullName }} ({{ $associated_user->username }})
                        </option>
                    @endforeach
                @endif
        </select>
    </div>

    {!! $errors->first('associated_users', '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>


<div class="col-md-12">
    @include ('partials.forms.edit.permissions-base', ['use_inherit' => false])
</div>

@stop
