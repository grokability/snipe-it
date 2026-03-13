@props([
    'id' => null,
])

<button type="submit" {{ $attributes->merge(['class' => 'btn']) }} {{ $attributes->merge(['id' => 'submit_button']) }}>
    {{ $slot  }}
</button>