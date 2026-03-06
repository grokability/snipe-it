@props([
    'route' => null,
    'name' => 'default',
])

<!-- start locations tab pane -->
@can('view', \App\Models\Location::class)

    <x-slot:header>
        {{ trans('general.locations') }}
    </x-slot:header>

    @include('partials.locations-bulk-actions')

    <x-slot:content>
        <x-table
                show_column_search="true"
                show_advanced_search="true"
                fixed_right_number="2"
                buttons="locationButtons"
                api_url="{{ $route }}"
                :presenter="\App\Presenters\LocationPresenter::dataTableLayout()"
                export_filename="export-{{ str_slug($name) }}-locations-{{ date('Y-m-d') }}"
        />
    </x-slot:content>

@endcan
<!-- end assets tab pane -->