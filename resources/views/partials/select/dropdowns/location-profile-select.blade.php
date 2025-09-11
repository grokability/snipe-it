<select class="js-data-ajax" data-endpoint="locations" data-placeholder="{{ trans('general.select_location') }}" name="location_id" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_location_profile_select' }}" aria-label="location_id">
    @if ($location_id = old('location_id', (isset($user)) ? $user->location_id : ''))
    <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Location::find($location_id)) ? \App\Models\Location::find($location_id)->name : '' }}
    </option>
    @else
    <option value="" role="option">{{ trans('general.select_location') }}</option>
    @endif
</select>