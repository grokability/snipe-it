@props([
    'route' => route('api.components.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\ComponentPresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 1,
    'table_header' => trans('general.components'),
    'export_name' => null,
])

@aware(['name'])

<!-- start components tab pane -->
@can('view', \App\Models\Component::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-slot:bulkactions>
        <x-table.bulk-components />
    </x-slot:bulkactions>

    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="componentButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ $export_name ? str_slug($export_name).'-' : '' }}components-{{ date('Y-m-d') }}"
    />

@endcan
<!-- end components tab pane -->