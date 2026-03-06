@extends('layouts/default')

{{-- Page title --}}
@section('title')

  {{ trans('admin/suppliers/table.view') }} -
  {{ $supplier->name }}

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

                    <x-tabs.asset-tab count="{{ $supplier->assets()->AssetsForShow()->count() }}" />
                    <x-tabs.license-tab count="{{ $supplier->licenses->count() }}" />
                    <x-tabs.accessory-tab count="{{ $supplier->accessories->count() }}" />
                    <x-tabs.consumable-tab count="{{ $supplier->consumables->count() }}" />
                    <x-tabs.component-tab count="{{ $supplier->components->count() }}" />

                    @can('view', \App\Models\AssetMaintenance::class)
                        <x-tabs.nav-item
                                name="maintenances"
                                icon="fa-solid fa-screwdriver-wrench"
                                label="{{ trans('general.maintenances') }}"
                                count="{{ $supplier->maintenances->count() }}"
                        />
                    @endcan

                <x-tabs.nav-item
                    name="files"
                    icon="fa-solid fa-file-contract fa-fw"
                    label="{{ trans('general.files') }}"
                    count="{{ $supplier->uploads()->count() }}"
                />

                @can('update', $supplier)
                    <x-tabs.nav-item-upload />
                @endcan

                </x-slot:tabnav>



                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    @can('view', \App\Models\Asset::class)
                        <x-tabs.pane name="assets" count="{{ $supplier->assets()->AssetsForShow()->count() }}">
                            <x-table.assets name="assets" :route="route('api.assets.index', ['supplier_id' => $supplier->id, 'itemtype' => 'assets'])" />
                        </x-tabs.pane>
                    @endcan
                    <!-- end assets tab pane -->

                    <!-- start licenses tab pane -->
                    @can('view', \App\Models\License::class)
                        <x-tabs.pane name="licenses" class="{{ $supplier->licenses->count() == 0 ? 'hidden-print' : '' }}">
                            <x-table.licenses name="licenses" :route="route('api.licenses.index', ['supplier_id' => $supplier->id])" />
                        </x-tabs.pane>
                    @endcan
                    <!-- end licenses tab pane -->

                    <!-- start accessories tab pane -->
                    @can('view', \App\Models\Accessory::class)
                        <x-tabs.pane name="accessories" class="{{ $supplier->accessories->count() == 0 ? 'hidden-print' : '' }}">
                            <x-table.accessories name="accessories" :route="route('api.accessories.index', ['supplier_id' => $supplier->id])" />
                        </x-tabs.pane>
                    @endcan
                    <!-- end accessories tab pane -->


                    <!-- start components tab pane -->
                    @can('view', \App\Models\Component::class)
                        <x-tabs.pane name="components" class="{{ $supplier->components->count() == 0 ? 'hidden-print' : '' }}">
                            <x-slot:header>
                                {{ trans('general.components') }}
                            </x-slot:header>


                                <x-table
                                        show_advanced_search="true"
                                        buttons="componentButtons"
                                        api_url="{{ route('api.components.index', ['supplier_id' => $supplier->id]) }}"
                                        :presenter="\App\Presenters\ComponentPresenter::dataTableLayout()"
                                        export_filename="export-{{ str_slug($supplier->name) }}-components-{{ date('Y-m-d') }}"
                                />

                        </x-tabs.pane>
                    @endcan
                    <!-- end components tab pane -->

                    <!-- start consumables tab pane -->
                    @can('view', \App\Models\Consumable::class)
                        <x-tabs.pane name="consumables" class="{{ $supplier->consumables->count() == 0 ? 'hidden-print' : '' }}">
                            <x-slot:header>
                                {{ trans('general.consumables') }}
                            </x-slot:header>

                            <x-slot:content>
                                <x-table
                                        show_advanced_search="true"
                                        buttons="consumableButtons"
                                        api_url="{{ route('api.consumables.index', ['supplier_id' => $supplier->id]) }}"
                                        :presenter="\App\Presenters\ConsumablePresenter::dataTableLayout()"
                                        export_filename="export-{{ str_slug($supplier->name) }}-consumables-{{ date('Y-m-d') }}"
                                />
                            </x-slot:content>
                        </x-tabs.pane>
                    @endcan
                    <!-- end consumables tab pane -->


                    <!-- start consumables tab pane -->
                    @can('view', \App\Models\Asset::class)
                        <x-tabs.pane name="maintenances" class="{{ $supplier->maintenances->count() == 0 ? 'hidden-print' : '' }}">
                            <x-slot:header>
                                {{ trans('admin/maintenances/general.maintenances') }}
                            </x-slot:header>

                                <x-table
                                        buttons="maintenanceButtons"
                                        api_url="{{ route('api.maintenances.index', ['supplier_id' => $supplier->id]) }}"
                                        :presenter="\App\Presenters\MaintenancesPresenter::dataTableLayout()"
                                        export_filename="export-{{ str_slug($supplier->name) }}-maintenances-{{ date('Y-m-d') }}"
                                />

                        </x-tabs.pane>
                    @endcan
                    <!-- end consumables tab pane -->

                    <!-- start files tab pane -->
                    <x-tabs.pane name="files" class="{{ $supplier->uploads->count() == 0 ? 'hidden-print' : '' }}">
                        <x-slot:header>
                            {{ trans('general.files') }}
                        </x-slot:header>
                        <x-slot:content>
                            <x-filestable object_type="suppliers" :object="$supplier" />
                        </x-slot:content>
                    </x-tabs.pane>
                    <!-- end files tab pane -->

                </x-slot:tabpanes>

            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3 hidden-print">

            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$supplier" img_path="{{ app('suppliers_upload_url') }}">

                    <x-slot:buttons>
                        <x-button :item="$supplier" permission="update" :route="route('suppliers.edit', $supplier->id)" class="btn-warning"  />
                        <x-button.delete :item="$supplier" />
                    </x-slot:buttons>


                </x-box.info-panel>
            </x-box>
        </x-page-column>

    </x-container>

    <div class="visible-print">
        <table style="margin-top: 80px;" class="signature-boxes">
            <tr>
                <td style="padding-right: 10px; vertical-align: top; font-weight: bold;">{{ trans('general.signed_off_by') }}:</td>
                <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
                <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
                <td>_____________</td>
            </tr>
            <tr style="height: 80px;">
                <td></td>
                <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.name') }}</td>
                <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.signature') }}</td>
                <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.date') }}</td>
            </tr>
            <tr>
                <td style="padding-right: 10px; vertical-align: top; font-weight: bold;">{{ trans('admin/users/table.manager') }}:</td>
                <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
                <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
                <td>_____________</td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.name') }}</td>
                <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.signature') }}</td>
                <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.date') }}</td>
                <td></td>
            </tr>

        </table>
    </div>

  @can('update', \App\Models\Supplier::class)
      @include ('modals.upload-file', ['item_type' => 'supplier', 'item_id' => $supplier->id])
  @endcan
@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table', [
      'exportFile' => 'locations-export',
      'search' => true
   ])

@stop
