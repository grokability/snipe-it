@props([
    'name' => 'country',
    'selected' => null,
])

@php
    $countries_array = trans('localizations.countries');
@endphp

<select
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'select2']) }}
    aria-label="{{ $name }}"
    data-placeholder="{{ trans('localizations.select_country') }}"
    data-allow-clear="true"
>
    @foreach($countries_array as $abbreviation => $country)
        @php
            // We have to handle it this way to handle deprecation warnings since you can't strtoupper on null
            if ($abbreviation!='') {
                $abbreviation = strtoupper($abbreviation);
            }
        @endphp

        <option value="{{ $abbreviation }}" {{ $selected === $abbreviation ? 'selected' : '' }}>
            {{ $country }}
        </option>
    @endforeach

    {{-- If the country value doesn't exist in the array, add it as a new option and select it so we don't drop that data --}}
    @if (!array_key_exists($selected, $countries_array)) {
        <option value="{{ e($selected) }}" selected="selected" role="option" aria-selected="true"> {{ e($selected) }} *</option> ';
    @endif

</select>
