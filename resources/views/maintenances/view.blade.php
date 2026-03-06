<?php
use Carbon\Carbon;
?>
@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/maintenances/general.view') }} {{ $maintenance->name }}
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

                    <x-tabs.nav-item
                            name="details"
                            class="active"
                            icon_type="info-circle"
                            label="{{ trans('general.details') }}"
                    />

                    <x-tabs.files-tab count="{{ $maintenance->uploads()->count() }}" />


                    @can('update', $maintenance)
                        <x-tabs.nav-item-upload />
                    @endcan

                </x-slot:tabnav>
                <x-slot:tabpanes>

                    <x-tabs.pane name="details" class="in active">


                        <div class="tab-pane active" id="info">
                            <div class="row-new-striped">
                                <div class="row">

                                    <div class="col-md-3">
                                        {{ trans('admin/maintenances/form.asset_maintenance_type') }}
                                    </div>
                                    <div class="col-md-9">
                                        {{ $maintenance->asset_maintenance_type }}
                                    </div>

                                </div> <!-- /row -->

                                <div class="row">
                                    <div class="col-md-3">
                                        {{ trans('general.asset') }}
                                    </div>
                                    <div class="col-md-9">
                                        <a href="{{ route('hardware.show', $maintenance->asset_id) }}">
                                            {{ $maintenance->asset?->present()->fullName }}
                                        </a>
                                    </div>
                                </div> <!-- /row -->

                                @if ($maintenance->asset->model)
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{ trans('general.asset_model') }}
                                        </div>
                                        <div class="col-md-9">
                                            <a href="{{ route('models.show', $maintenance->asset->model_id) }}">
                                                {{ $maintenance->asset?->model?->name }}
                                            </a>
                                        </div>
                                    </div> <!-- /row -->
                                @endif

                                @if ($maintenance->asset->company)
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{ trans('general.company') }}
                                        </div>
                                        <div class="col-md-9">
                                            <a href="{{ route('companies.show', $maintenance->asset->company_id) }}">
                                                {{ $maintenance->asset?->company?->name }}
                                            </a>
                                        </div>
                                    </div> <!-- /row -->
                                @endif



                                <div class="row">
                                    <div class="col-md-3">
                                        {{ trans('admin/maintenances/form.start_date') }}
                                    </div>
                                    <div class="col-md-9">
                                        {{ Helper::getFormattedDateObject($maintenance->start_date, 'date', false) }}
                                    </div>
                                </div> <!-- /row -->

                                <div class="row">
                                    <div class="col-md-3">
                                        {{ trans('admin/maintenances/form.completion_date') }}
                                    </div>
                                    <div class="col-md-9">
                                        @if ($maintenance->completion_date)
                                            {{ Helper::getFormattedDateObject($maintenance->completion_date, 'date', false) }}
                                        @else
                                            {{ trans('admin/maintenances/message.asset_maintenance_incomplete') }}
                                        @endif
                                    </div>
                                </div> <!-- /row -->

                                @if ($maintenance->url)
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{ trans('general.url') }}
                                        </div>
                                        <div class="col-md-9">
                                            <a href="{{ $maintenance->url }}">
                                                {{ $maintenance->url }}
                                                <x-icon type="external-link" />
                                            </a>
                                        </div>
                                    </div> <!-- /row -->
                                @endif

                                <div class="row">
                                    <div class="col-md-3">
                                        {{ trans('admin/maintenances/form.asset_maintenance_time') }}
                                    </div>
                                    <div class="col-md-9">
                                        {{ $maintenance->asset_maintenance_time }}
                                    </div>
                                </div> <!-- /row -->

                                @if ($maintenance->cost > 0)
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{ trans('admin/maintenances/form.cost') }}
                                        </div>
                                        <div class="col-md-9">
                                            {{ \App\Models\Setting::getSettings()->default_currency .' '. Helper::formatCurrencyOutput($maintenance->cost) }}
                                        </div>
                                    </div> <!-- /row -->
                                @endif

                                <div class="row">
                                    <div class="col-md-3">
                                        {{ trans('admin/maintenances/form.is_warranty') }}
                                    </div>
                                    <div class="col-md-9">
                                        {{ $maintenance->is_warranty ? trans('admin/maintenances/message.warranty') : trans('admin/maintenances/message.not_warranty') }}
                                    </div>
                                </div> <!-- /row -->

                                @if ($maintenance->notes)
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{ trans('admin/maintenances/form.notes') }}
                                        </div>
                                        <div class="col-md-9">
                                            {!! nl2br(Helper::parseEscapedMarkedownInline($maintenance->notes)) !!}
                                        </div>
                                    </div> <!-- /row -->
                                @endif


                            </div>
                        </div><!-- /row-new-striped -->

                    </x-tabs.pane>

                    <x-tabs.pane name="files">
                        <x-slot:header>
                            {{ trans('general.files') }}
                        </x-slot:header>
                        <x-slot:content>
                            <x-filestable object_type="maintenances" :object="$maintenance" />
                        </x-slot:content>
                    </x-tabs.pane>

                </x-slot:tabpanes>
            </x-tabs>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-box.info-panel :infoPanelObj="$maintenance" img_path="{{ app('maintenances_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$maintenance" :route="route('maintenances.edit', $maintenance->id)" />
                        <x-button.delete :item="$maintenance" />
                    </x-slot:buttons>

                </x-box.info-panel>
            </x-box>
        </x-page-column>
    </x-container>





  @can('assets.files', Asset::class)
    @include ('modals.upload-file', ['item_type' => 'maintenance', 'item_id' => $maintenance->id])
  @endcan
@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')
@stop

