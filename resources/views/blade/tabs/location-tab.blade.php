@props([
    'count' => null,
    'class' => false,

])

<x-tabs.nav-item
    :$class
    name="locations"
    icon_type="location"
    label="{{ trans('general.locations') }}"
    count="{{ $count }}"
    tooltip="{{ trans('general.locations') }}"
/>