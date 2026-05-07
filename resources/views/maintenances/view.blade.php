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
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')

    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.details-tab/>
                    <x-tabs.files-tab :item="$maintenance" count="{{ $maintenance->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $maintenance->history()->count() }}" :model="$maintenance"/>
                    <x-tabs.upload-tab :item="$maintenance"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start details tab content -->
                    <x-tabs.pane name="details">

                        <!-- this just adds a little top space -->
                        <div class="clearfix visible-lg-block" style="padding: 6px;"></div>

                        <!--  well column -->
                        <x-page-column class="col-md-4">
                            <x-well>
                                <x-info-element.status :infoObject="$maintenance->asset"/>
                            </x-well>
                        </x-page-column>
                        <!--  ./ well column -->

                        <!--  well column -->
                        <x-page-column class="col-md-4">
                            <x-well style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">
                                <x-icon type="asset" class="fa-fw"/>
                                {!! $maintenance->asset?->present()->nameUrl !!}
                            </x-well>
                        </x-page-column>
                        <!--  ./ well column -->

                        <!--  well column -->
                        <x-page-column class="col-md-4">
                            <x-well style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">
                                <x-icon type="maintenances" class="fa-fw"/>
                                <strong>{{ trans('admin/maintenances/form.asset_maintenance_type') }}</strong>
                                {{ $maintenance->asset_maintenance_type }}
                            </x-well>
                        </x-page-column>
                        <!--  ./ well column -->

                        <!-- set clearfix for responsive design -->
                        <div class="clearfix"></div>

                        <!-- definition list column -->
                        <x-page-column class="col-md-8 col-sm-12">

                            <!-- definition list content -->
                            <x-page-data>
                                <x-data-row :label="trans('admin/hardware/form.tag')" copy_what="asset_tag">
                                    {{ $maintenance->asset?->asset_tag }}
                                </x-data-row>

                                <x-data-row :label="trans('general.asset_model')" copy_what="model">
                                    {!! $maintenance->asset?->model?->present()->nameUrl !!}
                                </x-data-row>

                                <x-data-row :label="trans('general.model_no')" copy_what="model_number">
                                    {{ $maintenance->asset?->model?->model_number }}
                                </x-data-row>

                                <x-data-row :label="trans('general.start_date')" copy_what="start_date">
                                    {{ Helper::getFormattedDateObject($maintenance->start_date, 'date', false) }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/maintenances/form.completion_date')" copy_what="completion_date">
                                    @if ($maintenance->completion_date)
                                        {{ Helper::getFormattedDateObject($maintenance->completion_date, 'date', false) }}
                                    @else
                                        {{ trans('admin/maintenances/message.asset_maintenance_incomplete') }}
                                    @endif
                                </x-data-row>

                                <x-data-row :label="trans('admin/maintenances/form.asset_maintenance_time')" copy_what="time">
                                    @if ($maintenance->asset_maintenance_time)
                                        {{ $maintenance->asset_maintenance_time }} {{ trans('general.days') }}
                                    @endif
                                </x-data-row>

                                <x-data-row :label="trans('admin/maintenances/form.cost')" copy_what="cost">
                                    {{ $snipeSettings->default_currency .' '. Helper::formatCurrencyOutput($maintenance->cost) }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/maintenances/form.is_warranty')" copy_what="warranty_improvement">
                                    @if ($maintenance->is_warranty=='1')
                                        <x-icon type="checkmark" class="text-success"/>
                                        {{ trans('general.yes') }}
                                    @else
                                        <x-icon type="x" class="text-danger"/>
                                        {{ trans('general.no') }}
                                    @endif

                                </x-data-row>
                            </x-page-data>
                            <!-- ./ definition list content -->
                            <div class="clearfix"></div>
                        </x-page-column>
                        <!-- ./ definition list column -->

                            <!-- begin side stats well column-->
                            <x-page-column class="col-md-4 col-sm-12">

                                <x-well class="well-sm" style="padding-left: 15px;">
                                    @php

                                        $startCarbon = $maintenance->start_date ? Carbon::parse($maintenance->start_date) : null;
                                        $endCarbon   = $maintenance->completion_date
                                            ? Carbon::parse($maintenance->completion_date)
                                            : null;

                                        $maintenancePercent = 0;
                                        if ($startCarbon) {
                                             $progressLabel = App\Helpers\Helper::getFormattedDateObject($maintenance->start_date, 'date', false);
                                            if ($endCarbon) {
                                                 $progressLabel .= ' - '.App\Helpers\Helper::getFormattedDateObject($maintenance->completion_date, 'date', false);;
                                                // Completed: show how far through the total duration we are as of today
                                                $totalDays   = max(1, $startCarbon->diffInDays($endCarbon));
                                                $elapsedDays = min($totalDays, $startCarbon->diffInDays(Carbon::now()));
                                                $maintenancePercent = min(100, max(0, ($elapsedDays / $totalDays) * 100));
                                            } else {
                                                // In progress: base on days elapsed since start_date relative to 30-day window
                                                $elapsedDays = $startCarbon->diffInDays(Carbon::now());
                                                $maintenancePercent = min(100, max(0, ($elapsedDays / 30) * 100));
                                            }
                                        }
                                    @endphp


                                    <x-progressbar use_well="false" columns="12" :text="$progressLabel" :percent="$maintenancePercent">
                                    </x-progressbar>

                                </x-well>
                            </x-page-column>
                            <div class="clearfix"></div>


                    </x-tabs.pane>


                    <x-tabs.pane name="files">
                        <x-table.files object_type="maintenances" :object="$maintenance"/>
                    </x-tabs.pane>

                    <x-tabs.pane name="history">
                        <x-table.history :model="$maintenance" :route="route('api.maintenances.history', $maintenance)"/>
                    </x-tabs.pane>

                </x-slot:tabpanes>
            </x-tabs>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$maintenance" img_path="{{ app('maintenances_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$maintenance" :route="route('maintenances.edit', $maintenance->id)" />
                        <x-button.delete :item="$maintenance" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection


@section('moar_scripts')
    @can('files', $maintenance)
        @include ('modals.upload-file', ['item_type' => 'maintenances', 'item_id' => $maintenance->id])
    @endcan

    @include ('partials.bootstrap-table')
@endsection

