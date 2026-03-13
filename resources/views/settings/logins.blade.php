@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.login') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">

                    <table
                            data-cookie-id-table="loginReport"
                            data-id-table="loginReport"
                            data-search="false"
                            data-side-pagination="server"
                            data-sort-order="desc"
                            data-sort-name="created_at"
                            id="loginReport"
                            data-url="{{ route('api.settings.login_attempts') }}"
                            data-mobile-responsive="true"
                            class="table table-striped snipe-table"
                            data-export-options='{
                        "fileName": "login-report-{{ date('Y-m-d') }}"}'>

                        <thead>
                            <tr>
                                <th class="col-sm-2" data-field="username" data-visible="true" data-sortable="true">{{ trans('mail.username') }}</th>
                                <th class="col-sm-2" data-field="created_at" data-visible="true" data-sortable="true" data-formatter="dateDisplayFormatter">{{ trans('admin/settings/general.login_attempt') }}</th>
                                <th class="col-sm-2" data-field="user_agent" data-visible="true" data-sortable="true">{{ trans('admin/settings/general.login_user_agent') }}</th>
                                <th class="col-sm-2" data-field="remote_ip" data-visible="true" data-sortable="true">{{ trans('admin/settings/general.login_ip') }}</th>
                                <th class="col-sm-2" data-field="successful" data-visible="true" data-formatter="trueFalseFormatter" data-sortable="true">{{ trans('admin/settings/general.login_success') }}</th>
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
