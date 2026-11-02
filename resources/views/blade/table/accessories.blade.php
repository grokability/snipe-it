@props([
    'route' => route('api.accessories.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\AccessoryPresenter::dataTableLayout(['deleted_at']),
    'fixed_right_number' => 2,
    'fixed_number' => 2,
    'table_header' => trans('general.accessories'),
    'export_name' => null,
])

@aware(['name'])

<!-- start accessories tab pane -->
@can('view', \App\Models\Accessory::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-slot:bulkactions>
        <x-table.bulk-accessories />
    </x-slot:bulkactions>

    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="accessoryButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ $export_name ? str_slug($export_name).'-' : '' }}accessories-{{ date('Y-m-d') }}"
    />

@endcan
<!-- end accessories tab pane -->