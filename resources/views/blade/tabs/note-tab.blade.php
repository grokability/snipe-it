@props([
    'count' => null,
    'class' => false,
    'item',
])

@can('journal', $item)
    <x-tabs.nav-item
        :$class
        name="notes"
        icon_type="note"
        label="{{ trans('general.notes') }}"
        count="{{ $count }}"
        tooltip="{{ trans('general.notes') }}"
    />
@endcan