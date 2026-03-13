@props([
    'count' => null,
    'class' => false,
])

@can('view', \App\Models\Consumable::class)
    <x-tabs.nav-item
            :$class
            name="consumables"
            icon_type="consumable"
            label="{{ trans('general.consumables') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.consumables') }}"
    />
@endcan