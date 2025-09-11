<!-- Consumable -->
<div id="assigned_consumable" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!! isset($style) ? ' style="' . e($style) . '"' : '' !!}>
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7{{ isset($required) && $required == 'true' ? ' required' : '' }}">
        @include('partials.select/dropdowns/consumable-select')
    </div>
    {!! $errors->first(
        $fieldname,
        '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fas fa-times"></i> :message</span></div>',
    ) !!}

</div>
