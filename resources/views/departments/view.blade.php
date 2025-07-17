@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $department->name }}
    {{ trans('general.department') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('departments.edit', ['department' => $department->id]) }}" class="btn btn-sm btn-primary pull-right">{{ trans('admin/departments/table.update') }} </a>
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table table-responsive">

                                <table
                                        data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="departmentsUsersTable"
                                        data-id-table="departmentsUsersTable"
                                        data-show-footer="true"
                                        data-side-pagination="server"
                                        data-sort-order="asc"
                                        id="departmentsUsersTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.users.index',['department_id'=> $department->id]) }}"
                                        data-export-options='{
                              "fileName": "export-departments-{{ str_slug($department->name) }}-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>


                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table',
    ['exportFile' => 'departments-users-export',
    'search' => true,
    'columns' => \App\Presenters\UserPresenter::dataTableLayout()
])

@stop
