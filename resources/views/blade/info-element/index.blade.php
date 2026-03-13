@props([
    'icon' => null,
    'icon_type' => null,
    'icon_color' => null,
    'title' => null,
])

@if (!$slot->isEmpty())
    <li {{ $attributes->merge(['class' => 'list-group-item']) }}>

        @if ($icon_type)
            <x-icon type="{{ $icon_type }}" :title="$title" class="fa-fw" style="{{ 'color: '.$icon_color.' !important' ?? '' }}" />
        @elseif ($icon)
           <i class="{{ $icon }}"{!! $title ? ' data-tooltip="true" data-title="'.$title.'"' : '' !!}></i>
        @endif
        {{ $slot }}
    </li>
@endif