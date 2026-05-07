@extends('layouts/default')

{{-- Page title --}}
@section('title')

  {{ trans('admin/suppliers/table.view') }} -
  {{ $supplier->name }}

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

                    <x-tabs.asset-tab count="{{ $supplier->assets()->AssetsForShow()->count() }}" />
                    <x-tabs.license-tab count="{{ $supplier->licenses->count() }}" />
                    <x-tabs.accessory-tab count="{{ $supplier->accessories->count() }}" />
                    <x-tabs.consumable-tab count="{{ $supplier->consumables->count() }}" />
                    <x-tabs.component-tab count="{{ $supplier->components->count() }}" />
                    <x-tabs.maintenance-tab count="{{ $supplier->maintenances->count() }}"/>
                    <x-tabs.files-tab :item="$supplier" count="{{ $supplier->uploads()->count() }}"/>
                    <x-tabs.upload-tab :item="$supplier"/>

                </x-slot:tabnav>



                <x-slot:tabpanes>

                    <!-- start assets tab pane -->
                    <x-tabs.pane name="assets">
                        <x-table.assets name="assets" :route="route('api.assets.index', ['supplier_id' => $supplier->id, 'itemtype' => 'assets'])"/>
                    </x-tabs.pane>
                    <!-- end assets tab pane -->


                    <!-- start licenses tab pane -->
                    <x-tabs.pane name="licenses">
                        <x-table.licenses :name="$supplier->name" :route="route('api.licenses.index', ['supplier_id' => $supplier->id])"/>
                    </x-tabs.pane>
                    <!-- end licenses tab pane -->

                    <!-- start accessories tab pane -->
                    <x-tabs.pane name="accessories">
                        <x-table.accessories :name="$supplier->name" :route="route('api.accessories.index', ['supplier_id' => $supplier->id])"/>
                    </x-tabs.pane>
                    <!-- end accessories tab pane -->

                    <!-- start components tab pane -->
                    <x-tabs.pane name="components">
                        <x-table.accessories :name="$supplier->name" :route="route('api.components.index', ['supplier_id' => $supplier->id])"/>
                    </x-tabs.pane>
                    <!-- end components tab pane -->

                    <!-- start consumables tab pane -->
                    <x-tabs.pane name="consumables">
                        <x-table.consumables :name="$supplier->name" :route="route('api.consumables.index', ['supplier_id' => $supplier->id])"/>
                    </x-tabs.pane>
                    <!-- end consumables tab pane -->


                    <!-- start consumables tab pane -->
                    @can('view', \App\Models\Asset::class)
                        <x-tabs.pane name="maintenances" class="{{ $supplier->maintenances->count() == 0 ? 'hidden-print' : '' }}">
                            <x-slot:table_header>
                                {{ trans('admin/maintenances/general.maintenances') }}
                            </x-slot:table_header>

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
                        <x-table.files object_type="suppliers" :object="$supplier"/>
                    </x-tabs.pane>
                    <!-- end files tab pane -->

                </x-slot:tabpanes>

            </x-tabs>
        </x-page-column>
        <x-page-column class="col-md-3 hidden-print">

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$supplier" img_path="{{ app('suppliers_upload_url') }}">

                    <x-slot:buttons>
                        <x-button :item="$supplier" permission="update" :route="route('suppliers.edit', $supplier->id)" class="btn-warning"  />
                        <x-button.delete :item="$supplier" />
                    </x-slot:buttons>


                </x-info-panel>
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

@endsection

@section('moar_scripts')
    @can('files', $supplier)
        @include ('modals.upload-file', ['item_type' => 'suppliers', 'item_id' => $supplier->id])
    @endcan

    @include ('partials.bootstrap-table', ['exportFile' => 'suppliers-' . $supplier->name . '-export', 'search' => false])
@endsection

