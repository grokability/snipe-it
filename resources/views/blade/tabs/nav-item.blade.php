@props([
    'name' => null,
    'label' => null,
    'count' => 0,
    'icon' => null,
    'icon_style' => null,
    'tooltip' => null,
    'icon_type' => null,
])

<!-- start tab nav item -->
<li {{ $attributes->merge(['class' => 'snipetab']) }} role="presentation">


    <a href="#{{ $name ?? 'details' }}" data-toggle="tab" data-tooltip="true" title="{{ $tooltip ?? $label }}">

        @if ($icon_type || $icon)

            @if ($icon)
                <span class="hidden-lg hidden-md hidden-sm">
                    <i class="{{ $icon }}" style="font-size: 18px" aria-hidden="true"></i>
                    {{ $tooltip ?? $label }}
                </span>

                <span class="hidden-xs">
                    <i class="{{ $icon }}" style="font-size: 16px" aria-hidden="true"></i>
                </span>

            @elseif ($icon_type)

                <span class="hidden-lg hidden-md hidden-sm">
                    <x-icon type="{{ $icon_type }}" class="fa-fw" style="font-size: 18px;" />
                    {{ $tooltip ?? $label }}
                </span>

                <span class="hidden-xs">
                    <x-icon type="{{ $icon_type }}" class="fa-fw" style="font-size: 16px;" />
                </span>

            @endif

            <span class="sr-only">
                {{ $label }}
            </span>

        @elseif ($label)
            {{ $label }}
        @endif


        @if ($count > 0)
            <span class="badge">{{ number_format($count) }}</span>
        @endif

    </a>
</li>
<!-- end tab nav item -->