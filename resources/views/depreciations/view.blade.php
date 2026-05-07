@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.depreciation') }}: {{ $depreciation->name }} ({{ $depreciation->months }} {{ trans('general.months') }})

    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.asset-tab count="{{ $depreciation->assets()->AssetsForShow()->count() }}"/>
                    <x-tabs.license-tab count="{{ $depreciation->licenses->count() }}"/>
                    <x-tabs.model-tab count="{{ $depreciation->models->count() }}"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    <x-tabs.pane name="assets">
                        <x-table.assets name="assets" :route="route('api.assets.index', ['depreciation_id' => $depreciation->id])"/>
                    </x-tabs.pane>
                    <!-- end assets tab pane -->


                    <!-- start licenses tab pane -->
                    <x-tabs.pane name="licenses">
                        <x-table.licenses name="licenses" :route="route('api.licenses.index', ['depreciation_id' => $depreciation->id])"/>
                    </x-tabs.pane>
                    <!-- end licenses tab pane -->

                    <!-- start models tab pane -->
                    @can('view', \App\Models\AssetModel::class)
                        <x-tabs.pane name="models">
                            <x-table.models :route="route('api.models.index', ['status' => e(request('status')), 'depreciation_id' => $depreciation->id])"/>
                        </x-tabs.pane>
                    @endcan
                    <!-- end licenses tab pane -->



                </x-slot:tabpanes>

            </x-tabs>



        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$depreciation">

                    <x-slot:buttons>
                        <x-button.edit :item="$depreciation" :route="route('depreciations.edit', $depreciation->id)" />
                        <x-button.delete :item="$depreciation" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>

        </x-page-column>

    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
