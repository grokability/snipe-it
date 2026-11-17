@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $model->name }}
    {{ ($model->model_number) ? '(#'.$model->model_number.')' : '' }}
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">

        @if ($model->deleted_at!='')
            <div class="col-md-12">
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans('admin/models/general.deleted') }}
                </x-callout>
            </div>
        @endif

        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.asset-tab count="{{ $model->assets()->AssetsForShow()->count() }}" />
                    <x-tabs.files-tab :item="$model" count="{{ $model->uploads()->count() }}"/>
                    <x-tabs.orders-tab count="{{ $model->ordersCount() }}"/>
                    <x-tabs.history-tab count="{{ $model->history()->count() }}" :model="$model"/>
                    <x-tabs.upload-tab :item="$model"/>
                </x-slot:tabnav>


                <x-slot:tabpanes>
                    <x-tabs.pane name="assets">
                        <x-table.assets name="modelAssets" :route="route('api.assets.index', ['model_id' => $model->id, 'status' => $model->deleted_at!='' ? 'Deleted' : ''])" />
                    </x-tabs.pane>

                    <!-- start orders tab pane -->
                    <x-tabs.pane name="orders">
                        <x-table.orders name="modelOrders" :route="route('api.order-items.index', ['asset_model_id' => $model->id])"/>
                    </x-tabs.pane>
                    <!-- end orders tab pane -->

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        {{-- AssetModel history is limited to model-level
                             events (create / update / delete on the
                             model itself). Models aren't checked out
                             and don't get files uploaded to them, so
                             hide the columns that only carry data for
                             per-asset checkout / adjust-quantity flows.
                             `item` is redundant since every row is
                             this same model. --}}
                        <x-table.history name="modelHistory"
                            :model="$model"
                            :route="route('api.models.history', $model)"
                            :hide_fields="['order_number', 'target', 'item', 'serial', 'file', 'file_download', 'signature_file', 'quantity']"
                        />
                    </x-tabs.pane>
                    <!-- end history tab pane -->

                    <x-tabs.pane name="files">
                        <x-table.files :object="$model" object_type="models" />
                    </x-tabs.pane>
                </x-slot:tabpanes>
            </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :fieldset="$fieldset" :infoPanelObj="$model" img_path="{{ app('models_upload_url') }}" :qr_code_url="route('qr_code/common', ['object_type' => 'models', 'id' => $model->id])">
                    <x-slot:buttons>
                        <x-button.edit :item="$model" :route="route('models.edit', $model->id)" />
                        <x-button.add :item="\App\Models\Asset::class" :tooltip="trans('general.new_asset')" :route="route('hardware.create', ['model_id' => $model->id])"/>
                        <x-button.restore :item="$model" :route="route('models.restore.store', $model->id)" />
                        <x-button.clone :item="$model" :route="route('models.clone.create', $model->id)" />
                        <x-button.delete :item="$model" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection

@section('moar_scripts')
    @can('files', $model)
        <x-modals.upload-file item-type="models" :item-id="$model->id" />
    @endcan

    @include ('partials.bootstrap-table', ['exportFile' => 'models-' . $model->name . '-export', 'search' => false])
@endsection
