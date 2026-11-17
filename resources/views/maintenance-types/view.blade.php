@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $item->name }} {{ trans('admin/maintenances/general.maintenances') }}
    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-box name="maintenances">

                <x-slot:bulkactions>
                    <x-table.bulk-maintenances name="maintenances" />
                </x-slot:bulkactions>

                <x-table.maintenances
                    name="maintenances"
                    :table_header="trans('admin/maintenances/general.maintenances')"
                    :route="route('api.maintenances.index', ['maintenance_type_id' => $item->id])"
                />
            </x-box>
        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$item">

                    <x-slot:buttons>
                        <x-button.edit :item="$item" :route="route('maintenance-types.edit', $item->id)" />
                        <x-button.delete :item="$item" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'maintenances-export', 'search' => true])
    <x-modals.maintenance-complete />
@stop
