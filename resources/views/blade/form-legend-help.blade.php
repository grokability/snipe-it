@props([
    'icon' => null,
])

<!-- Form Legend Help Component -->
<p class="callout-subtext">
    <x-icon type="{{ $icon ?? 'tip' }}" class="text-info" />
    {!! $slot !!}
</p>
