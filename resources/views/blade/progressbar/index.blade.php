@props([
    'text' => null,
    'percent' => null,
    'columns' => 4,
    'size' => 'sm',
    'positive' => false,
    'use_well' => 'true',
    'color' => null,
    'warning_icon' => false,
    'warning_label' => null,
])

<?php
// Explicit color wins over the percentage-driven class so when
// representing a terminal state (completed maintenance, cancelled
// request) we can pin a success / muted color without faking the
// percentage.
if ($color !== null) {
    $color_class = $color;
} elseif ($percent < 25) {
    $color_class = (!$positive ? 'info' : 'danger');
} elseif ($percent < 75) {
    $color_class = (!$positive ? 'warning' : 'info');
} else {
    $color_class = (!$positive ? 'danger' : 'info');
}

?>


    <!-- start progress bar -->
<div class="row">
<div class="col-md-{{ $columns }}">
    {!! ($use_well=='true') ? '<div class="well well-sm">' : '' !!}
    <div class="progress-group">
        <span class="progress-text">
            @if ($warning_icon)
                {{-- Icon is aria-hidden via <x-icon>, so the sibling sr-only
                     span carries the semantic for screen readers. Caller
                     supplies warning_label with the context-specific string
                     (e.g. "Overdue") so the component stays generic. --}}
                <x-icon type="warning" class="text-yellow" data-tooltip="true" title="{{ $warning_label }}"/>
                <span class="sr-only">{{ $warning_label }}</span>
            @endif
            {{ $text }}
        </span>
        <span class="progress-number">
            {{ $slot }}
            <span class="text-muted">{{ round($percent) }}%</span>
        </span>
        <div class="progress {{ $size }}">
            <div class="progress-bar progress-bar-{{ $color_class }}" role="progressbar" style="width: {{ round($percent) }}%"></div>
        </div>
    </div>
    {!! ($use_well=='true') ? '</div>' : '' !!}
</div>
</div>
<!-- end progress bar -->
