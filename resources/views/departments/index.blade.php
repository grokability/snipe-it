@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.departments') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>
            <x-table
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
