@props([
    'route' => route('api.users.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\UserPresenter::dataTableLayout(),
    'fixed_right_number' => 1,
    'fixed_number' => 2,
    'table_header' => trans('general.users'),
])

<!-- start assets tab pane -->
@can('view', \App\Models\User::class)
    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-slot:bulkactions>
        <x-table.bulk-users/>
    </x-slot:bulkactions>

    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="userButtons"
        api_url="{{ $route }}"
        export_filename="export-users-{{ date('Y-m-d') }}"
    />


@endcan
<!-- end assets tab pane -->