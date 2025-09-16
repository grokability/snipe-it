<select class="js-data-ajax select2" data-endpoint="predefinedFilters"
    data-placeholder="{{ trans('general.select_predefined_filter') }}" name="{{ $fieldname }}" style="width: 100%"
    id="{{ (isset($select_id)) ? $select_id : 'predefined_filter_select' }}" {{  ((isset($required) && ($required == 'true'))) ? ' required' : '' }}>
    <option value="">{{ trans('general.select_accessory') }}</option>
</select>