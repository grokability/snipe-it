@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ $manufacturer->name }}
 {{ trans('general.manufacturer') }}
@parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.asset-tab count="{{ $manufacturer->assets()->AssetsForShow()->count() }}" />
                    <x-tabs.license-tab count="{{ $manufacturer->licenses->count() }}" />
                    <x-tabs.accessory-tab count="{{ $manufacturer->accessories->count() }}" />
                    <x-tabs.consumable-tab count="{{ $manufacturer->consumables->count() }}" />
                    <x-tabs.component-tab count="{{ $manufacturer->components->count() }}" />
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assets" class="in active">
                        <x-table.assets :route="route('api.assets.index', ['manufacturer_id' => $manufacturer->id, 'itemtype' => 'assets'])" />
                    </x-tabs.pane>

                    <x-tabs.pane name="licenses">
                        <x-table.licenses :name="$manufacturer->name" :route="route('api.licenses.index', ['manufacturer_id' => $manufacturer->id])" />
                    </x-tabs.pane>

                    <x-tabs.pane name="accessories">
                        <x-table.accessories :name="$manufacturer->name" :route="route('api.accessories.index', ['manufacturer_id' => $manufacturer->id])" />
                    </x-tabs.pane>

                    <x-tabs.pane name="consumables">
                        <x-table.consumables :name="$manufacturer->name" :route="route('api.consumables.index', ['manufacturer_id' => $manufacturer->id])" />
                    </x-tabs.pane>

                    <x-tabs.pane name="components">
                        <x-table.components :name="$manufacturer->name" :route="route('api.components.index', ['manufacturer_id' => $manufacturer->id])" />
                    </x-tabs.pane>

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$manufacturer" img_path="{{ app('manufacturers_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$manufacturer" :route="route('manufacturers.edit', $manufacturer->id)" />
                        <x-button.delete :item="$manufacturer" />
                    </x-slot:buttons>

                </x-box.info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@stop


@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'manufacturer' . $manufacturer->name . '-export', 'search' => false])

@stop
