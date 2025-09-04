<!-- partials/forms/edit/name-last.blade.php -->
@php
    $class = $class ?? 'col-md-6';
    $style = $style ?? '';
    $required = $required ?? '';
    $value = $value ?? $user->last_name;
@endphp
<div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
    <label class="col-md-3 control-label" for="last_name">{{ trans('general.last_name') }} </label>
    <div class="{{$class}}" style= "{{$style ? $style : ''}}">
        <input class="form-control" type="text" name="last_name" id="last_name"  value="{{ old('last_name', ($value ?? $user->last_name)) }}" maxlength="191"{{  (Helper::checkIfRequired($user, 'last_name')) ? ' required' : '' }} />
        {!! $errors->first('last_name', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
</div>
<!-- partials/forms/edit/name-last.blade.php -->