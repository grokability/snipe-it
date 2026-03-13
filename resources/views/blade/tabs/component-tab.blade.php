@props([
    'count' => null,
    'class' => false,
])

@can('view', \App\Models\Component::class)
    <x-tabs.nav-item
            :$class
            name="components"
            icon_type="component"
            label="{{ trans('general.components') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.components') }}"
    />
@endcan