<!-- Accessory -->
<div id="assigned_accessory" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7">
        <select class="js-data-ajax select2" data-endpoint="accessories" data-placeholder="{{ trans('general.select_accessory') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ (isset($select_id)) ? $select_id : 'assigned_accessory_select' }}"{{ (isset($multiple)) ? ' multiple' : '' }}{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}>

            @if ((!isset($unselect)) && ($accessory_id = old($fieldname, (isset($accessory) ? $accessory->id  : (isset($item) ? $item->{$fieldname} : '')))))
                <option value="{{ $accessory_id }}" selected="selected">
                    {{ (\App\Models\Accessory::find($accessory_id)) ? \App\Models\Accessory::find($accessory_id)->present()->name : '' }}
                </option>
            @else
                @if(!isset($multiple))
                    <option value="">{{ trans('general.select_accessory') }}</option>
                @endif
            @endif
        </select>
    </div>
    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fas fa-times"></i> :message</span></div>') !!}

</div>
