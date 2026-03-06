@extends('layouts/default')

{{-- Page title --}}
@section('title')

{{ $category->name }}

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
                    @if ($category->category_type=='asset')
                        @can('view', \App\Models\Asset::class)

                            <x-tabs.nav-item
                                    name="assets"
                                    class="active"
                                    icon_type="asset"
                                    label="{{ trans('general.assets') }}"
                                    count="{{ $category->showableAssets()->count() }}"
                            />

                            <x-tabs.nav-item
                                    name="models"
                                    icon_type="models"
                                    label="{{ trans('general.asset_models') }}"
                                    count="{{ $category->models->count() }}"
                            />
                        @endcan

                    @elseif ($category->category_type=='accessory')

                        @can('view', \App\Models\Accessory::class)
                            <x-tabs.nav-item
                                    name="accessories"
                                    class="active"
                                    icon_type="accessory"
                                    label="{{ trans('general.accessories') }}"
                                    count="{{ $category->accessories()->count() }}"
                            />
                        @endcan

                    @elseif ($category->category_type=='license')
                        @can('view', \App\Models\License::class)

                            <x-tabs.nav-item
                                    name="licenses"
                                    class="active"
                                    icon_type="license"
                                    label="{{ trans('general.licenses') }}"
                            />
                        @endcan

                    @elseif ($category->category_type=='consumable')

                        @can('view', \App\Models\Consumable::class)
                            <x-tabs.nav-item
                                    name="consumables"
                                    class="active"
                                    icon_type="consumable"
                                    label="{{ trans('general.consumables') }}"
                                    count="{{ $category->consumables()->count() }}"
                            />
                        @endcan

                    @elseif ($category->category_type=='component')

                        @can('view', \App\Models\Component::class)
                            <x-tabs.nav-item
                                    name="components"
                                    class="active"
                                    icon_type="component"
                                    label="{{ trans('general.components') }}"
                                    count="{{ $category->components()->count() }}"
                            />
                        @endcan
                    @endif




                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    @if ($category->category_type=='asset')
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
                                            api_url="{{ route('api.assets.index', ['category_id' => $category->id, 'itemtype' => 'assets']) }}"
                                            :presenter="\App\Presenters\AssetPresenter::dataTableLayout()"
                                            export_filename="export-{{ str_slug($category->name) }}-assets-{{ date('Y-m-d') }}"
                                    />
                                </x-slot:content>

                            </x-tabs.pane>

                            <x-tabs.pane name="models">
                                <x-slot:header>
                                    {{ trans('general.asset_models') }}
                                </x-slot:header>

                                <x-slot:bulkactions>
                                    @include('partials.models-bulk-actions')
                                </x-slot:bulkactions>

                                <x-slot:content>
                                    <x-table
                                        buttons="modelButtons"
                                        api_url="{{ route('api.models.index', ['status' => e(request('status')), 'category_id' => $category->id]) }}"
                                        :presenter="\App\Presenters\AssetModelPresenter::dataTableLayout()"
                                        export_filename="export-{{ str_slug($category->name) }}-models-{{ date('Y-m-d') }}"
                                    />
                                </x-slot:content>

                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='license')
                        @can('view', \App\Models\License::class)
                            <x-tabs.pane name="licenses" class="in active">
                                <x-slot:header>
                                    {{ trans('general.licenses') }}
                                </x-slot:header>

                                <x-slot:content>
                                    <x-table
                                            buttons="licenseButtons"
                                            api_url="{{ route('api.licenses.index', ['category_id' => $category->id]) }}"
                                            :presenter="\App\Presenters\LicensePresenter::dataTableLayout()"
                                            export_filename="export-{{ str_slug($category->name) }}-licenses-{{ date('Y-m-d') }}"
                                    />
                                </x-slot:content>

                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='accessory')
                        @can('view', \App\Models\Accessory::class)
                            <x-tabs.pane name="accessories" class="in active">
                                <x-slot:header>
                                    {{ trans('general.accessories') }}
                                </x-slot:header>


                                <x-slot:content>
                                    <x-table
                                        buttons="accessoryButtons"
                                        api_url="{{ route('api.accessories.index', ['category_id' => $category->id]) }}"
                                        :presenter="\App\Presenters\AccessoryPresenter::dataTableLayout()"
                                        export_filename="export-{{ str_slug($category->name) }}-accessories-{{ date('Y-m-d') }}"
                                    />
                                </x-slot:content>

                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='consumable')
                        @can('view', \App\Models\Consumable::class)
                            <x-tabs.pane name="consumables" class="in active">
                                <x-slot:header>
                                    {{ trans('general.consumables') }}
                                </x-slot:header>

                                <x-slot:content>
                                    <x-table
                                            buttons="consumableButtons"
                                            api_url="{{ route('api.consumables.index', ['category_id' => $category->id]) }}"
                                            :presenter="\App\Presenters\ConsumablePresenter::dataTableLayout()"
                                            export_filename="export-{{ str_slug($category->name) }}-consumables-{{ date('Y-m-d') }}"
                                    />
                                </x-slot:content>

                            </x-tabs.pane>
                        @endcan

                    @elseif ($category->category_type=='component')
                        @can('view', \App\Models\Component::class)
                            <x-tabs.pane name="components" class="in active">
                                <x-slot:header>
                                    {{ trans('general.components') }}
                                </x-slot:header>

                                <x-slot:content>
                                    <x-table
                                            buttons="componentButtons"
                                            api_url="{{ route('api.components.index', ['category_id' => $category->id]) }}"
                                            :presenter="\App\Presenters\ComponentPresenter::dataTableLayout()"
                                            export_filename="export-{{ str_slug($category->name) }}-components-{{ date('Y-m-d') }}"
                                    />
                                </x-slot:content>

                            </x-tabs.pane>
                        @endcan
                    @endif
                    <!-- end assets tab pane -->

                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>
    <x-page-column class="col-md-3">

        <x-box class="side-box expanded">
            <x-box.info-panel :infoPanelObj="$category" img_path="{{ app('categories_upload_url') }}">

                <x-slot:buttons>
                    <x-button.edit :item="$category" :route="route('categories.edit', $category->id)" />
                    <x-button.delete :item="$category" />
                </x-slot:buttons>

            </x-box.info-panel>
        </x-box>
    </x-page-column>
</x-container>

@endsection
@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
