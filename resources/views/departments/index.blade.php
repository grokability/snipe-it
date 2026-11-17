@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.departments') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="department" sr_only_title>

            <x-slot:table_header>{{ trans('general.departments') }}</x-slot:table_header>

            <x-slot:bulkactions>
                <x-table.bulk-departments />
            </x-slot:bulkactions>

            <x-table
                    name="department"
                    show_column_search="false"
                    show_advanced_search="true"
                    buttons="departmentButtons"
                    fixed_right_number="1"
                    fixed_number="1"
                    api_url="{{ route('api.departments.index') }}"
                    :presenter="\App\Presenters\DepartmentPresenter::dataTableLayout()"
                    export_filename="export-departments-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop


@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <x-table.advanced-search
        tableId="departmentListingTable"
        :model="\App\Models\Department::class"
    />
@stop
