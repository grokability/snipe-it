@extends('layouts/default')
{{-- Page title --}}
@section('title')

    @if (request('status')=='deleted')
        {{ trans('general.deleted') }}
    @else
        {{ trans('general.current') }}
    @endif
    {{ trans('general.users') }}
    @parent

@stop

@section('header_right')

    @can('create', \App\Models\User::class)
        @if ($snipeSettings->ldap_enabled == 1)
            <a href="{{ route('ldap/user') }}" class="btn btn-default pull-right"><span class="fas fa-sitemap"></span>{{trans('general.ldap_sync')}}</a>
        @endif
        <a href="{{ route('users.create') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "n" : ''}} class="btn btn-primary pull-right" style="margin-right: 5px;">  {{ trans('general.create') }}</a>
    @endcan

    @if (request('status')=='deleted')
        <a class="btn btn-default pull-right" href="{{ route('users.index') }}" style="margin-right: 5px;">{{ trans('admin/users/table.show_current') }}</a>
    @else
        <a class="btn btn-default pull-right" href="{{ route('users.index', ['status' => 'deleted']) }}" style="margin-right: 5px;">{{ trans('admin/users/table.show_deleted') }}</a>
    @endif
    @can('view', \App\Models\User::class)
        <a class="btn btn-default pull-right" href="{{ route('users.export') }}" style="margin-right: 5px;">{{ trans('general.export') }}</a>
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
                    data-advanced-search="true"
                    data-click-to-select="true"
                    data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                    data-cookie-id-table="usersTable"
                    data-pagination="true"
                    data-id-table="usersTable"
                    data-search="true"
                    data-search-highlight="true"
                    data-show-print="true"
                    data-side-pagination="server"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-refresh="true"
                    data-sort-order="asc"
                    data-toolbar="#userBulkEditToolbar"
                    data-bulk-button-id="#bulkUserEditButton"
                    data-bulk-form-id="#usersBulkForm"
                    id="usersTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.users.index',
              array('deleted'=> (request('status')=='deleted') ? 'true' : 'false','company_id' => e(request('company_id')))) }}"
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
