@extends('layouts/default')
{{-- Page title --}}
@section('title')

    @if (request('status')=='deleted')
        {{ trans('general.deleted') }}
    @elseif (request('admins')=='true')
        {{ trans('general.show_admins') }}
    @elseif (request('superadmins')=='true')
        {{ trans('general.show_superadmins') }}
    @else
        {{ trans('general.current') }}
    @endif
    {{ trans('general.users') }}
    @parent

@stop

@section('header_right')

    @can('create', \App\Models\User::class)
        @if ($snipeSettings->ldap_enabled == 1)
            <a href="{{ route('ldap/user') }}" class="btn btn-default pull-right"><i class="fas fa-sitemap"></i> {{trans('general.ldap_sync')}}</a>
        @endif
    @endcan
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
        <div class="box-body">

            @include('partials.users-bulk-actions')

            <table
                    data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                    data-cookie-id-table="usersTable"
                    data-id-table="usersTable"
                    data-side-pagination="server"
                    data-toolbar="#userBulkEditToolbar"
                    data-bulk-button-id="#bulkUserEditButton"
                    data-bulk-form-id="#usersBulkForm"
                    data-show-columns-search="true"
                    id="usersTable"
                    data-buttons="userButtons"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.users.index',
                        [
                            'status' => e(request('status')),
                            'deleted'=> (request('status')=='deleted') ? 'true' : 'false',
                            'company_id' => e(request('company_id')),
                            'manager_id' => e(request('manager_id')),
                            'admins' => e(request('admins')),
                            'superadmins' => e(request('superadmins')),
                            'activated' => e(request('activated')),
                       ]) }}"
                    data-export-options='{
                "fileName": "export-users-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>


@stop

@section('moar_scripts')


@include ('partials.bootstrap-table')


@stop
