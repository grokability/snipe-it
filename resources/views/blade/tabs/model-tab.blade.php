@props([
    'count' => null,
])

<x-tabs.nav-item
    name="models"
    icon_type="models"
    label="{{ trans('general.asset_models') }}"
    count="{{ $count }}"
    tooltip="{{ trans('general.asset_models') }}"
/>
