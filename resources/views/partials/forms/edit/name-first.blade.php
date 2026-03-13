<!-- partials/forms/edit/name-first.blade.php -->
@php
    $class = $class ?? 'col-md-6';
    $style = $style ?? '';
    $required = $required ?? '';
    $value = $value ?? $user->first_name;
@endphp
<div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
    <label class="col-md-3 control-label" for="first_name">{{ trans('general.first_name') }}</label>
    <div class="{{$class ? $class : 'col-md-6'}}" style= "{{$style}}">
        <input class="form-control" type="text" name="first_name" id="first_name" value="{{ old('first_name', ($value ?? $user->first_name)) }}" {{$required ? 'required' : ''}} maxlength="191" {{  (Helper::checkIfRequired($user, 'first_name')) ? ' required' : '' }}/>
        {!! $errors->first('first_name', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
</div>
<!-- partials/forms/edit/name-first.blade.php -->