@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/maintenances/general.asset_maintenances') }}
  @parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>

        <x-table.maintenances
            :route="route('api.maintenances.index').'?completed='.request()->input('completed', 'false').'&upcoming_status='.request()->input('upcoming_status', '')"
        />

        </x-box>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'maintenances-export', 'search' => true])
    <x-modals.maintenance-complete />
@stop
