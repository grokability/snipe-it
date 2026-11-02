@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/maintenances/general.asset_maintenances') }}
  @parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="maintenance" sr_only_title>

            <x-slot:bulkactions>
                <x-table.bulk-maintenances
                    name="maintenance"
                    :show-complete="request()->input('completed') !== 'true'"
                />
            </x-slot:bulkactions>

            <x-table.maintenances
                name="maintenance"
                :route="route('api.maintenances.index').'?completed='.request()->input('completed', 'false').'&upcoming_status='.request()->input('upcoming_status', '')"
                :hide_fields="request('completed') === 'true' ? [] : ['completed_at', 'completed_by']"
            />

        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'maintenances-export', 'search' => true])
    <x-modals.maintenance-complete />

    <x-table.advanced-search
        tableId="maintenanceListingTable"
        :model="\App\Models\Maintenance::class"
    />
@stop
