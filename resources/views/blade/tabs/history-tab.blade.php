@props([
    'count' => null,
    'model' => null,
    'class' => false,
])

@can('view', $model)
    <x-tabs.nav-item
            :$class
            name="history"
            icon_type="history"
            label="{{ trans('general.history') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.history') }}"
    />
@endcan