@props([
    'route' => null,
    'name' => 'default',
])

<!-- start consumables tab pane -->
@can('view', \App\Models\Consumable::class)

    <x-slot:header>
        {{ trans('general.consumables') }}
    </x-slot:header>

    <x-slot:content>
        <x-table
                show_column_search="true"
                show_advanced_search="true"
                buttons="consumableButtons"
                api_url="{{ $route }}"
                :presenter="\App\Presenters\ConsumablePresenter::dataTableLayout()"
                export_filename="export-{{ str_slug($name) }}-consumables-{{ date('Y-m-d') }}"
        />
    </x-slot:content>

@endcan
<!-- end consumables tab pane -->