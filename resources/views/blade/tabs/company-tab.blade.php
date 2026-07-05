@props([
    'count' => null,
    'class' => false,
])

@can('view', \App\Models\Company::class)
    <x-tabs.nav-item
            :$class
            name="companies"
            icon_type="company"
            label="{{ trans('general.companies') }}"
            count="{{ $count }}"
            tooltip="{{ trans('general.companies') }}"
    />
@endcan
