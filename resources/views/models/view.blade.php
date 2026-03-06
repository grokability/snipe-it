@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $model->name }}
    {{ ($model->model_number) ? '(#'.$model->model_number.')' : '' }}
@parent
@stop

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">

        @if ($model->deleted_at!='')
            <div class="col-md-12">
                <div class="callout callout-warning">
                    <x-icon type="warning" />
                    {{ trans('admin/models/general.deleted') }}
                </div>
            </div>
        @endif

        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.asset-tab count="{{ $model->assets()->AssetsForShow()->count() }}" />
                    <x-tabs.files-tab name="files" count="{{ $model->uploads()->count() }}" />

                    @can('update', $model)
                        <x-tabs.nav-item-upload />
                    @endcan
                </x-slot:tabnav>


                <x-slot:tabpanes>
                    <x-tabs.pane name="assets" class="in active">
                        <x-table.assets :route="route('api.assets.index', ['model_id' => $model->id])" />
                    </x-tabs.pane>

                    <x-tabs.pane name="files">
                        <x-filestable :object="$model" object_type="models" />
                    </x-tabs.pane>
                </x-slot:tabpanes>
            </x-tabs>

        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$model" img_path="{{ app('models_upload_url') }}">
                    <x-slot:buttons>
                        <x-button.edit :item="$model" :route="route('models.edit', $model->id)" />
                        <x-button.clone :item="$model" :route="route('models.clone.create', $model->id)" />
                        <x-button.delete :item="$model" />
                    </x-slot:buttons>

                </x-box.info-panel>
            </x-box>
        </x-page-column>
    </x-container>



@can('update', \App\Models\AssetModel::class)
    @include ('modals.upload-file', ['item_type' => 'models', 'item_id' => $model->id])
@endcan
@stop

@section('moar_scripts')

    @include ('partials.bootstrap-table', ['exportFile' => 'manufacturer' . $model->name . '-export', 'search' => false])

@stop
