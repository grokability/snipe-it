@props([
    'color' => null,
    'type' => 'square',
    'title' => null,
])

@php $_tagColorSafe = \App\Helpers\Helper::safeColor($color); @endphp

@if ($_tagColorSafe !== '')
    <x-icon :type="$type" class="fa-fw" :title="$title" style="color: {{ $_tagColorSafe }} !important;" />
@endif
