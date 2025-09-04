@props([
    'name',
    'selected',
    // Whether to include a blank option with the label "Site Default"
    'includeBlankOption' => false,
])

@php
    $formats = [
        'blue' => trans('general.skins.default_blue'),
        'blue-dark' => trans('general.skins.blue_dark'),
        'green' => trans('general.skins.green'),
        'green-dark' => trans('general.skins.green_dark'),
        'red' => trans('general.skins.red'),
        'red-dark' => trans('general.skins.red_dark'),
        'orange' => trans('general.skins.orange'),
        'orange-dark' => trans('general.skins.orange_dark'),
        'black' => trans('general.skins.black'),
        'black-dark' => trans('general.skins.black_dark'),
        'purple' => trans('general.skins.purple'),
        'purple-dark' => trans('general.skins.purple_dark'),
        'yellow' => trans('general.skins.yellow'),
        'yellow-dark' => trans('general.skins.yellow_dark'),
        'contrast' => trans('general.skins.high_contrast'),
    ];

    if ($includeBlankOption) {
        $formats = ['' => trans('general.skins.site_default')] + $formats;
    }
@endphp

<select
    name="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'select2',
        'style' => 'width: 250px;',
    ]) }}
>
    @foreach ($formats as $value => $label)
        <option
            value="{{ $value }}"
            @selected($value == $selected)
        >
            {{ $label }}
        </option>
    @endforeach
</select>
