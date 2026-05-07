@props([
    'route' => route('api.licenses.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\LicensePresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 1,
    'show_search' => true,
    'show_advanced_search' => false,
    'show_column_search' => false,
    'table_header' => trans('general.licenses'),
])

<!-- start licenses tab pane -->
@can('view', \App\Models\License::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>


    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        :$show_search
        :$show_column_search
        :$show_advanced_search
        buttons="licenseButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-licenses-{{ date('Y-m-d') }}"
    />


@endcan
<!-- end licenses tab pane -->