<select class="js-data-ajax" data-endpoint="locations" data-placeholder="{{ trans('general.select_location') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_location_select' }}"  aria-label="{{ $fieldname }}" {{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}{!! ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' required ' : '' !!}>
    @isset($selected)
    @foreach($selected as $location_id)
    <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Location::find($location_id))->name }}
    </option>
    @endforeach
    @endisset
    @if ($location_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Location::find($location_id)) ? \App\Models\Location::find($location_id)->name : '' }}
    </option>
    @endif
</select>
