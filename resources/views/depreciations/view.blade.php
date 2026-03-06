@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.depreciation') }}: {{ $depreciation->name }} ({{ $depreciation->months }} {{ trans('general.months') }})

    @parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    @can('view', \App\Models\Asset::class)
                        <x-tabs.nav-item
                                class="active"
                                name="assets"
                                icon_type="asset"
                                label="{{ trans('general.assets') }}"
                                count="{{ $depreciation->assets()->AssetsForShow()->count() }}"
                                tooltip="{{ trans('general.assets') }}"
                        />
                    @endcan

                    @can('view', \App\Models\License::class)
                        <x-tabs.nav-item
                                name="licenses"
                                icon_type="licenses"
                                label="{{ trans('general.licenses') }}"
                                count="{{ $depreciation->licenses()->count() }}"
                                tooltip="{{ trans('general.licenses') }}"
                        />
                    @endcan

                    @can('view', \App\Models\AssetModel::class)
                        <x-tabs.nav-item
                                name="models"
                                icon="fa-solid fa-boxes-packing"
                                label="{{ trans('general.asset_models') }}"
                                count="{{ $depreciation->models_count }}"
                                tooltip="{{ trans('general.asset_models') }}"
                        />
                    @endcan

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    @can('view', \App\Models\Asset::class)
                        <x-tabs.pane name="assets" class="in active">
                            <x-slot:header>
                                {{ trans('general.assets') }}
                            </x-slot:header>

                            <x-slot:bulkactions>
                                <x-table.bulk-assets />
                            </x-slot:bulkactions>

                            <x-slot:content>
                                <x-table
                                        show_column_search="true"
                                        show_advanced_search="true"
                                        buttons="assetButtons"
                                        api_url="{{ route('api.assets.index', ['depreciation_id' => $depreciation->id]) }}"
                                        :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                                        export_filename="export-depreciation-{{ str_slug($depreciation->name) }}-assets-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                        <!-- end assets tab pane -->
                    @endcan


                    <!-- start licenses tab pane -->
                    @can('view', \App\Models\License::class)
                        <x-tabs.pane name="licenses">
                            <x-slot:header>
                                {{ trans('general.licenses') }}
                            </x-slot:header>
                            <x-slot:content>
                                <x-table
                                        name="licenses"
                                        buttons="licenseButtons"
                                        api_url="{{ route('api.licenses.index', ['depreciation_id' => $depreciation->id]) }}"
                                        :presenter="\App\Presenters\LicensePresenter::dataTableLayout()"
                                        export_filename="export-depreciation-{{ str_slug($depreciation->name) }}-licences-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end licenses tab pane -->

                    <!-- start models tab pane -->
                    @can('view', \App\Models\AssetModel::class)
                        <x-tabs.pane name="models">
                            <x-slot:header>
                                {{ trans('general.models') }}
                            </x-slot:header>
                            <x-slot:content>
                                <x-table
                                        name="models"
                                        buttons="modelButtons"
                                        api_url="{{ route('api.models.index', ['depreciation_id' => $depreciation->id]) }}"
                                        :presenter="\App\Presenters\AssetModelPresenter::dataTableLayout()"
                                        export_filename="export-depreciation-{{ str_slug($depreciation->name) }}-models-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end licenses tab pane -->



                </x-slot:tabpanes>

            </x-tabs>



        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$depreciation">

                    <x-slot:buttons>
                        <x-button.edit :item="$depreciation" :route="route('depreciations.edit', $depreciation->id)" />
                        <x-button.delete :item="$depreciation" />
                    </x-slot:buttons>

                </x-box.info-panel>
            </x-box>

        </x-page-column>

    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
