@props([
    'count' => null,
    'class' => false,
])

<x-tabs.nav-item
    :$class
    name="eulas"
    icon_type="eulas"
    label="{{ trans('general.eula') }}"
    count="{{ $count }}"
    tooltip="{{ trans('general.eula') }}"
/>