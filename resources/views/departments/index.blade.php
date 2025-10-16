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
                            <thead>
                            <tr>
                                <th data-sortable="true" data-field="id" data-visible="false">{{ trans('general.id') }}</th>
                                <th data-sortable="true" data-field="company" data-visible="false" data-formatter="companiesLinkObjFormatter">{{ trans('general.company') }}</th>
                                <th data-sortable="true" data-formatter="departmentsLinkFormatter" data-field="name" data-searchable="false">{{ trans('admin/departments/table.name') }}</th>
                                <th data-sortable="true" data-field="image" data-visible="false" data-formatter="imageFormatter">{{ trans('general.image') }}</th>
                                <th data-sortable="true" data-formatter="usersLinkObjFormatter" data-field="manager" data-searchable="false">{{ trans('admin/departments/table.manager') }}</th>
                                <th data-sortable="true" data-field="users_count" data-searchable="false">{{ trans('general.users') }}</th>
                                <th data-sortable="true" data-formatter="locationsLinkObjFormatter" data-field="location" data-searchable="false">{{ trans('admin/departments/table.location') }}</th>
                                <th data-sortable="true" data-field="notes" data-searchable="true">{{ trans('general.notes') }}</th>
                                <th data-sortable="false" data-formatter="departmentsActionsFormatter" data-field="actions" data-searchable="false">{{ trans('table.actions') }}</th>

                            </tr>
                            </thead>
                        </table>
                </div>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
