@props([
    'route' => route('api.accessories.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\AccessoryPresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 1,
    'table_header' => trans('general.accessories'),
])

@aware(['name'])

<!-- start accessories tab pane -->
@can('view', \App\Models\Accessory::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>
    
    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="accessoryButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-accessories-{{ date('Y-m-d') }}"
    />

@endcan
<!-- end accessories tab pane -->