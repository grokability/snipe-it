@use('Illuminate\Support\Str')

@props([
    'label',
    'content_class' => 'col-md-6',
])

@php
    $labelId = 'static-' . Str::slug($label);
@endphp

<div class="form-group">
    <label id="{{ $labelId }}" class="col-sm-3 control-label">{{ $label }}</label>
    <div class="{{ $content_class }}">
        <p class="form-control-static" aria-labelledby="{{ $labelId }}">{{ $slot }}</p>
    </div>
</div>
