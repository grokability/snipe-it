@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.departments') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="department">

            <x-slot:bulkactions>
                <x-table.bulk-actions
                        name='department'
                        action_route="{{ route('departments.bulk.delete') }}"
                        model_name="department"
                >
                    @can('delete', App\Models\Department::class)
                        <option>{{ trans('general.delete') }}</option>
                    @endcan
                </x-table.bulk-actions>
            </x-slot:bulkactions>

            <x-table
                    name="department"
                    show_column_search="false"
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

@stop
