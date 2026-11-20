@props([
    'icon' => null,
    'icon_type' => null,
    'icon_color' => null,
    'title' => null,
])

@if (!$slot->isEmpty())
    <li {{ $attributes->merge(['class' => 'list-group-item']) }} id="{{ strtolower(str_slug($title)) }}">

        @if ($icon_type)
            @if (\App\Helpers\Helper::safeColor($icon_color) !== '')
                <x-tag-color :color="$icon_color" :type="$icon_type" :title="$title" />
            @else
                <x-icon :type="$icon_type" :title="$title" class="fa-fw" />
            @endif
        @elseif ($icon)
           <i class="{{ $icon }}"{!! $title ? ' data-tooltip="true" data-title="'.$title.'"' : '' !!}></i>
        @endif
        {{ $slot }}
    </li>
@endif