@props([
    'count' => null,
    'class' => false,
])
@aware(['class'])

@can('view', \App\Models\User::class)
    <x-tabs.nav-item
            :$class
            name="users"
            icon_type="user"
            label="{{ trans('general.users') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.users') }}"
    />
@endcan