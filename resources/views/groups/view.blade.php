@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $group->name }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('groups.edit', ['group' => $group->id]) }}" class="btn btn-primary text-right">{{ trans('admin/groups/titles.update') }} </a>
    <a href="{{ route('groups.index') }}" class="btn btn-default pull-right">{{ trans('general.back') }}</a>
@stop



{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">


                                <table
                                    data-columns="{{  \App\Presenters\UserPresenter::dataTableLayout() }}"
                                    data-cookie-id-table="groupsUsersTable"
                                    data-side-pagination="server"
                                    id="groupsUsersTable"
                                    class="table table-striped snipe-table"
                                    data-url="{{ route('api.users.index',['group_id'=> $group->id]) }}"
                                    data-export-options='{
                                    "fileName": "export-{{ str_slug($group->name) }}-group-users-{{ date('Y-m-d') }}",
                                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                                        }'>
                                </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">

            @if (is_array($group->decodePermissions()))
            <ul class="list-unstyled">
                @foreach ($group->decodePermissions() as $permission_name => $permission)
                   <li>{!! ($permission == '1') ? '<i class="fas fa-check text-success" aria-hidden="true"></i><span class="sr-only">'.trans('general.yes').': </span>' :  '<i class="fas fa-times text-danger" aria-hidden="true"></i><span class="sr-only">'.trans('general.no').': </span>' !!} {{ e(str_replace('.', ': ', ucwords($permission_name))) }} </li>
                @endforeach

            </ul>
            @else
                <p>{{ trans('admin/groups/titles.no_permissions') }}</p>
            @endif

        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
