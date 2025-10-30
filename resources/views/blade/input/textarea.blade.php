@props([
    'value' => '',
    'rows' => 5,
])

<textarea
    {{ $attributes->merge(['class' => 'form-control']) }}
    rows="{{ $rows }}"
>{{ $value }}</textarea>
