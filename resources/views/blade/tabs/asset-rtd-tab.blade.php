@props([
    'count' => null,
    'class' => false,
])

@can('view', \App\Models\Asset::class)
    <x-tabs.nav-item
            :$class
            name="rtd_assets"
            icon="fa-solid fa-house-flag fa-fw"
            label="{{ trans('admin/hardware/form.default_location') }}"
            count="{{ $count }}"
            tooltip="{{ trans('admin/hardware/form.default_location') }}"
    />
@endcan