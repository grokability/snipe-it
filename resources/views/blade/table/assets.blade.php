@props([
    'route' => route('api.assets.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\AssetPresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 1,
    'table_header' => trans('general.assets'),
])

@aware(['name'])


<!-- start assets tab pane -->
@can('view', \App\Models\Asset::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-slot:bulkactions>
        <x-table.bulk-assets/>
    </x-slot:bulkactions>
    
    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="assetButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-assets-{{ date('Y-m-d') }}"
    />

@endcan
<!-- end assets tab pane -->