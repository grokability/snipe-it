@props([
    'route' => route('api.companies.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\CompanyPresenter::dataTableLayout(),
    'fixed_right_number' => 1,
])

<!-- start companies tab pane -->
@can('view', \App\Models\Company::class)
    <x-table
        :$presenter
        :$fixed_right_number
        show_column_search="true"
        show_advanced_search="false"
        buttons="companyButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-companies-{{ date('Y-m-d') }}"
    />
@endcan
<!-- end companies tab pane -->
