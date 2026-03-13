@props([
    'route' => null,
    'name' => 'default',
])

<!-- start assets tab pane -->
@can('view', \App\Models\Asset::class)

        <x-slot:header>
            {{ trans('general.assets') }}
        </x-slot:header>

        <x-slot:bulkactions>
            <x-table.bulk-assets />
        </x-slot:bulkactions>

        <x-slot:content>
            <x-table
                    show_column_search="true"
                    show_advanced_search="true"
                    fixed_right_number="2"
                    buttons="assetButtons"
                    api_url="{{ $route }}"
                    :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                    export_filename="export-{{ str_slug($name) }}-assets-{{ date('Y-m-d') }}"
            />
        </x-slot:content>

@endcan
<!-- end assets tab pane -->