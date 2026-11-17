@props([
    'route' => route('api.locations.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\LocationPresenter::dataTableLayout(),
    'fixed_right_number' => 1,
    'table_header' => trans('general.locations'),
    'export_name' => null,
])

@aware(['name'])

<!-- start locations tab pane -->
@can('view', \App\Models\Location::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-slot:bulkactions>
        <x-table.bulk-locations />
    </x-slot:bulkactions>

    <x-table
        :$presenter
        :$fixed_right_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="locationButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ $export_name ? str_slug($export_name).'-' : '' }}locations-{{ date('Y-m-d') }}"
    />


@endcan
<!-- end locations tab pane -->