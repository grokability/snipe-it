@props([
    'count' => null,
    'name' => 'users',
    'icon_type' => 'users',
    'label' => trans('general.users'),

])
@aware(['class'])

@can('view', \App\Models\User::class)
    <x-tabs.nav-item
        name="{{ $name }}"
        icon_type="{{ $icon_type }}"
        label="{{ trans('general.users') }}"
        count="{{ $count }}"
        tooltip="{{ $tooltip ?? $label }}"
    />
@endcan