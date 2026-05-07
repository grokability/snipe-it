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
            <a href="{{ route('ldap/user') }}" class="btn btn-theme pull-right"><i class="fas fa-sitemap"></i> {{trans('general.ldap_sync')}}</a>
        @endif
    @endcan
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>
            <x-table.users :route="route('api.users.index',
                [
                    'status' => is_scalar(request('status')) ? request('status') : null,
                    'deleted'=> (request('status')=='deleted') ? 'true' : 'false',
                    'company_id' => is_scalar(request('company_id')) ? request('company_id') : null,
                    'manager_id' => is_scalar(request('manager_id')) ? request('manager_id') : null,
                    'admins' => is_scalar(request('admins')) ? request('admins') : null,
                    'superadmins' => is_scalar(request('superadmins')) ? request('superadmins') : null,
                    'activated' => is_scalar(request('activated')) ? request('activated') : null,
               ])"/>
        </x-box>
    </x-container>


@stop

@section('moar_scripts')

    @include ('partials.bootstrap-table')

@stop
