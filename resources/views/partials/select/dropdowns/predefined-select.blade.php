<select class="js-data-ajax select2" data-endpoint="predefinedFilters"
    data-placeholder="{{ trans('general.select_predefinedfilters') }}" name="{{ $fieldname }}" style="width: 100%"
    id="{{ (isset($select_id)) ? $select_id : 'predefined_filter_select' }}" {{  ((isset($required) && ($required == 'true'))) ? ' required' : '' }}>

    @if ((!isset($unselect)) && ($predefined_filter_id = old($fieldname, (isset($accessory) ? $predefined_filter_id->id : (isset($item) ? $item->{$fieldname} : '')))))
        <option value="{{ $predefined_filter_id }}" selected="selected">
            {{ (\App\Models\PredefinedFilter::find($predefined_filter_id)) ? \App\Models\PredefinedFilter::find($predefined_filter_id)->present()->name : '' }}
        </option>
    @else
        @if(!isset($multiple))
            <option value="">{{ trans('general.select_accessory') }}</option>
        @endif
    @endif
</select>