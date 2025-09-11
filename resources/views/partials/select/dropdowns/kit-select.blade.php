<select class="js-data-ajax" data-endpoint="kits" data-placeholder="{{ trans('partials/forms/general.placeholder_kit') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_kit_id_select' }}" {{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}>
    @if ($kit_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $kit_id }}" selected="selected">
        {{ (\App\Models\User::find($kit_id)) ? \App\Models\User::find($kit_id)->present()->fullName : '' }}
    </option>
    @else
    <option value="">{{ trans('partials/forms/general.placeholder_kit') }}</option>
    @endif
</select>