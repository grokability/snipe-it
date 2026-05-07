@props([
    'route' => route('api.locations.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\LocationPresenter::dataTableLayout(),
    'fixed_right_number' => 1,
    'table_header' => trans('general.locations'),
])

<!-- start locations tab pane -->
@can('view', \App\Models\Location::class)


    <x-slot:bulkactions>
        <x-table.bulk-actions
            name='location'
            action_route="{{ route('locations.bulkdelete.show') }}"
            model_name="location">
            @can('delete', App\Models\Location::class)
                <option>{{ trans('general.delete') }}</option>
            @endcan
        </x-table.bulk-actions>
    </x-slot:bulkactions>
    
    <x-table
        :$presenter
        :$fixed_right_number
        show_column_search="true"
        show_advanced_search="false"
        buttons="locationButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-locations-{{ date('Y-m-d') }}"
    />


@endcan
<!-- end locations tab pane -->