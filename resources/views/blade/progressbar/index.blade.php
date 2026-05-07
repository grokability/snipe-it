@props([
    'text' => null,
    'percent' => null,
    'columns' => 4,
    'size' => 'sm',
    'positive' => false,
    'use_well' => 'true',
])

<?php
if ($percent < 25) {
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
        <span class="progress-text">{{ $text }}</span>
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
