@props([
    'route' => route('api.models.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\AssetModelPresenter::dataTableLayout(),
    'fixed_right_number' => 1,
    'fixed_number' => 2,
    'table_header' => trans('general.asset_models'),
])

<!-- start assets tab pane -->
@can('view', \App\Models\AssetModel::class)
    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-slot:bulkactions>
        <x-table.bulk-models/>
    </x-slot:bulkactions>
    
    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        buttons="modelButtons"
        api_url="{{ $route }}"
        export_filename="export-models-{{ date('Y-m-d') }}"
    />


@endcan
<!-- end assets tab pane -->