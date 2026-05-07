@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $group->name }}
    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">

    <x-page-column class="col-md-9 main-panel">
            <x-box>
                <x-table.users name="groupsUsersTable" :route="route('api.users.index', ['group_id' => $group->id])"/>
            </x-box>
        </x-page-column>

        <x-page-column class="col-md-3 hidden-print">

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$group">

                    <x-slot:buttons>
                        <x-button :item="$group" permission="update" :route="route('groups.edit', $group->id)" class="btn-warning"/>
                        <x-button.delete :item="$group"/>
                    </x-slot:buttons>

                    @if (is_array($group->decodePermissions()))
                            @foreach ($group->decodePermissions() as $permission_name => $permission)
                                <li class="list-group-item">{!! ($permission == '1') ? '<i class="fas fa-check text-success" aria-hidden="true"></i><span class="sr-only">'.trans('general.yes').': </span>' :  '<i class="fas fa-times text-danger" aria-hidden="true"></i><span class="sr-only">'.trans('general.no').': </span>' !!} {{ e(str_replace('.', ': ', ucwords($permission_name))) }} </li>
                            @endforeach
                    @else
                        <p>{{ trans('admin/groups/titles.no_permissions') }}</p>
                    @endif

                </x-info-panel>
            </x-box>
        </x-page-column>

    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
