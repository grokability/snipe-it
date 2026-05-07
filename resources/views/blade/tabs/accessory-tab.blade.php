@props([
    'count' => null,
    'class' => false,
])

@can('view', \App\Models\Accessory::class)
    <x-tabs.nav-item
            :$class
            name="accessories"
            icon_type="accessory"
            label="{{ trans('general.accessories') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.accessories') }}"
    />
@endcan