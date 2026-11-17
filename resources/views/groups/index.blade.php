@extends('layouts/default')

{{-- Web site Title --}}
@section('title')
{{ trans('admin/groups/titles.group_management') }}
@parent
@stop




{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="groups" sr_only_title>

            <x-slot:table_header>{{ trans('general.groups') }}</x-slot:table_header>

            <x-table
                    name="groups"
                    buttons="groupButtons"
                    fixed_right_number="1"
                    fixed_number="1"
                    show_advanced_search="true"
                    api_url="{{ route('api.groups.index') }}"
                    :presenter="\App\Presenters\GroupPresenter::dataTableLayout()"
                    export_filename="export-groups-{{ date('Y-m-d') }}"
            />
            
        </x-box>
    </x-container>
@stop
@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'groups-export', 'search' => true])

<x-table.advanced-search
    tableId="groupsListingTable"
    :model="\App\Models\Group::class"
/>
@stop
