@props([
    'type' => 'info',
    'icon' => null,
    'live' => 'polite',
    'role' => 'alert',
])
<div {{ $attributes->merge(['class' => 'callout callout-'.$type]) }} role="{{ $role }}" aria-live="{{ $live }}" aria-atomic="true">

        @if ($icon)
            <x-icon :type="$icon" />
        @endif
        {{ $slot }}

</div>
