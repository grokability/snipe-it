@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.departments') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                        <table
                                data-columns="{{ \App\Presenters\DepartmentPresenter::dataTableLayout() }}"
                                data-cookie-id-table="departmentsTable"
                                data-id-table="departmentsTable"
                                data-side-pagination="server"
                                data-sort-order="asc"
                                id="departmentsTable"
                                data-advanced-search="false"
                                data-buttons="departmentButtons"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.departments.index') }}"
                                data-export-options='{
                              "fileName": "export-departments-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

                        </table>
                </div>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
