@props([
'class' => 'col-md-12',
])

<!-- Start column component -->
<div class="{{ $class }}">
    {{ $slot }}
</div>
