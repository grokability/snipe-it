@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.asset_maintenance_report') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>

            <x-table
                nosticky="true"
                name="maintenanceReport"
                api_url="{{ route('api.maintenances.index', ['format' => 'flat']) }}"
                :presenter="\App\Presenters\MaintenancesPresenter::reportLayout()"
                export_filename="export-maintenances-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
