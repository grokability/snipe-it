@props([
    'count' => 0,
    'model' => null,
    'class' => false,
])

@can('history', $model)
    @if ($count > 0)
    <x-tabs.nav-item
            :$class
            name="history"
            icon_type="history"
            label="{{ trans('general.history') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.history') }}"
    />
    @endif
@endcan