{{-- IMPORTANT!!! Make sure there is no newline at the end of this file, or it will break the loaders for the tables --}}
@props([
    'type' => '',
    'title' => false,
])
<i {{ $attributes->merge(['class' => Icon::icon($type)]) }} aria-hidden="true"{!! $title ? ' data-tooltip="true" data-title="'.$title.'"' : '' !!}></i>