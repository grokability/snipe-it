@props([
    'type' => 'info',
    'icon' => null,
    'title' => null,
    'role' => 'alert',
])
<div {{ $attributes->merge(['class' => 'alert alert-'.$type.' fade in']) }} role="{{ $role }}">
    @if ($icon)
        <x-icon :type="$icon" />
    @endif
    @if ($title)
        <strong>{{ $title }}:</strong>
    @endif
    {{ $slot }}
</div>
