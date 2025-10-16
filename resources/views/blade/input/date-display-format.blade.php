@php
    $formats = [
        'Y-m-d',
        'D M d, Y',
        'M j, Y',
        'd M, Y',
        'm/d/Y',
        'n/d/y',
        'd/m/Y',
        'd.m.Y',
        'Y.m.d.',
    ];

    foreach ($formats as $format) {
        $date_display_formats[$format] = Carbon::parse(date('Y-m-d'))->format($format);
    }
@endphp

<x-input.select {{ $attributes }}>
    @foreach($date_display_formats as $format => $date_display_format)
        <option
            value="{{ $format }}"
            @selected($selected == $format)
            role="option"
        >
            {{ $date_display_format }}
        </option>
    @endforeach
</x-input.select>
