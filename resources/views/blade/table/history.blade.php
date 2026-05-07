@props([
    'route',
    'name' => 'default',
    'table_header' => trans('general.history'),
    'model' => null,
    'hide_fields' => [],
])

<!-- start history tab pane -->
@can('history', $model)
    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-table
        :presenter="\App\Presenters\HistoryPresenter::dataTableLayout($hide_fields)"
        show_advanced_search="false"
        api_url="{{ $route }}"
        fixed_number="false"
        fixed_right_number="false"
        export_filename="export-history-{{ date('Y-m-d') }}"
    />
@endcan
<!-- end assets tab pane -->