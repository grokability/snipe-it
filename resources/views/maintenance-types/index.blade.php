@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/maintenance_types/general.maintenance_types') }}
@parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="maintenancetype" sr_only_title>

            <x-slot:table_header>{{ trans('admin/maintenance_types/general.maintenance_types') }}</x-slot:table_header>

            <x-slot:bulkactions>
                <x-table.bulk-maintenance-types />
            </x-slot:bulkactions>

            <x-table
                    name="maintenancetype"
                    buttons="maintenanceTypeButtons"
                    fixed_right_number="1"
                    fixed_number="1"
                    show_advanced_search="true"
                    api_url="{{ route('api.maintenance-types.index') }}"
                    :presenter="\App\Presenters\MaintenanceTypePresenter::dataTableLayout()"
                    export_filename="export-maintenance-types-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <x-table.advanced-search
        tableId="maintenancetypeListingTable"
        :model="\App\Models\MaintenanceType::class"
    />
@stop
