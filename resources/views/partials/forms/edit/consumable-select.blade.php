<!-- Consumable -->
<div id="assigned_consumable" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}">
        <select class="js-data-ajax select2" data-endpoint="consumables" data-placeholder="{{ trans('general.select_consumable') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ (isset($select_id)) ? $select_id : 'assigned_consumable_select' }}"{{ (isset($multiple)) ? ' multiple' : '' }}>
        
            @if ((!isset($unselect)) && ($consumable_id = old($fieldname, (isset($consumable) ? $consumable->id  : (isset($item) ? $item->{$fieldname} : '')))))
                <option value="{{ $consumable_id }}" selected="selected">
                    {{ (\App\Models\Consumable::find($consumable_id)) ? \App\Models\Consumable::find($consumable_id)->present()->name : '' }}
                </option>
            @else
                @if(!isset($multiple))
                    <option value="">{{ trans('general.select_consumable') }}</option>
                @endif
            @endif
        </select>
    </div>
    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fas fa-times"></i> :message</span></div>') !!}

</div>
