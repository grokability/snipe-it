<select class="js-data-ajax select2" data-endpoint="consumables" data-placeholder="{{ trans('general.select_consumable') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ (isset($select_id)) ? $select_id : 'assigned_consumable_select' }}" {{ (isset($multiple)) ? ' multiple' : '' }}>

    @if ((!isset($unselect)) && ($consumable_id = old($fieldname, (isset($consumable) ? $consumable->id : (isset($item) ? $item->{$fieldname} : '')))))
    <option value="{{ $consumable_id }}" selected="selected">
        {{ (\App\Models\Consumable::find($consumable_id)) ? \App\Models\Consumable::find($consumable_id)->present()->name : '' }}
    </option>
    @else
    @if(!isset($multiple))
    <option value="">{{ trans('general.select_consumable') }}</option>
    @endif
    @endif
</select>