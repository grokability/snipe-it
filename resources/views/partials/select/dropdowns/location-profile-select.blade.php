{{-- Some pages won't set the $fieldname. If this is the case the fallback-value of "default_location_profile_select" is used. --}}
<select class="js-data-ajax"
    data-endpoint="locations"

    @isset($fieldname)
        data-placeholder="{{ trans('general.select_location') }}"
    @endisset

    name="location_id"
    style="width: 100%"

    id="{{ $select_id ?? (isset($fieldname) ? $fieldname . '_location_profile_select' : 'default_location_profile_select') }}"

    aria-label="location_id"

    @isset($fieldname)
        data-tags="{{ !empty($allow_tags) ? 'true' : 'false' }}"
    @endisset
>

    @if ($location_id = old('location_id', isset($user) ? $user->location_id : ''))
        <option value="{{ $location_id }}" selected aria-selected="true" role="option">
            {{ optional(\App\Models\Location::find($location_id))->name }}
        </option>
    @else
        <option value="" role="option">{{ trans('general.select_location') }}</option>
    @endif

</select>
