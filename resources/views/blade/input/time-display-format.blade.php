@php
    $formats = [
        'g:iA',
        'h:iA',
        'H:i',
    ];

    $datetime = date("y-m-d").' 14:00:00';

    foreach ($formats as $format) {
        $time_display_formats[$format] = Carbon::parse($datetime)->format($format);
    }
@endphp

<x-input.select {{ $attributes }}>
    @foreach ($time_display_formats as $format => $time_display_format)
        <option
            value="{{ $format }}"
            @selected($selected == $format)
        >
            {{ $time_display_format }}
        </option>
    @endforeach
</x-input.select>
