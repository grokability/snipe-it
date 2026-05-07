@props([
    'route' => route('api.consumables.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\ConsumablePresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 1,
    'table_header' => trans('general.consumables'),
])

<!-- start consumables tab pane -->
@can('view', \App\Models\Consumable::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="consumableButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-consumables-{{ date('Y-m-d') }}"
    />

@endcan
<!-- end consumables tab pane -->