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
                    <x-tabs.note-tab :item="$maintenance" count="{{ $maintenance->journal->count() }}"/>
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
                                    {!!  $maintenance->asset?->present()->formattedTagLink !!}
                                </x-data-row>

                                <x-data-row :label="trans('general.asset_model')" copy_what="model">
                                    {!! $maintenance->asset?->model?->present()->nameUrl !!}
                                </x-data-row>

                                <x-data-row :label="trans('general.model_no')" copy_what="model_number">
                                    {{ $maintenance->asset?->model?->model_number }}
                                </x-data-row>

                                <x-data-row :label="trans('general.start_date')" copy_what="start_date">
                                    {{ Helper::getFormattedDateObject($maintenance->start_date, 'datetime', false) }}
                                </x-data-row>

                                <x-data-row :label="trans('admin/maintenances/form.completion_date')" copy_what="expected_completion_date">
                                    @if ($maintenance->expected_completion_date)
                                        {{ Helper::getFormattedDateObject($maintenance->expected_completion_date, 'datetime', false) }}
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

                                <x-data-row :label="trans('admin/maintenances/form.is_warranty')">
                                    @if ($maintenance->is_warranty=='1')
                                        <x-icon type="checkmark" class="text-success"/>
                                        {{ trans('general.yes') }}
                                    @else
                                        <x-icon type="x" class="text-danger"/>
                                        {{ trans('general.no') }}
                                    @endif
                                </x-data-row>

                                @if ($maintenance->responsibleParty)
                                    <x-data-row :label="trans('admin/maintenances/form.responsible_party')" copy_what="responsible_party">
                                        {!! $maintenance->responsibleParty->present()->nameUrl() !!}
                                    </x-data-row>
                                @endif

                                @if ($maintenance->checkedOutTo)
                                    <x-data-row :label="trans('admin/maintenances/form.checked_out_to_at_creation')">
                                        <x-icon type="{{ strtolower(class_basename($maintenance->checked_out_to_type)) }}" class="fa-fw"/>
                                        {!! $maintenance->checkedOutTo->present()->formattedNameLink() !!}

                                        <p class="help-block">
                                            {{ trans('admin/maintenances/general.checked_out_to_help') }}
                                        </p>
                                    </x-data-row>
                                @endif

                                @if ($maintenance->completed_at)
                                    <x-data-row :label="trans('admin/maintenances/form.completed_at')">
                                        {{ Helper::getFormattedDateObject($maintenance->completed_at, 'datetime', false) }}
                                    </x-data-row>
                                    @if ($maintenance->completedByUser)
                                        <x-data-row :label="trans('admin/maintenances/form.completed_by')">
                                            {!! $maintenance->completedByUser->present()->nameUrl() !!}
                                        </x-data-row>
                                    @endif
                                @endif

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
                                        $endCarbon   = $maintenance->expected_completion_date
                                            ? Carbon::parse($maintenance->expected_completion_date)
                                            : null;

                                        $maintenancePercent = 0;
                                        if ($startCarbon) {
                                             $progressLabel = App\Helpers\Helper::getFormattedDateObject($maintenance->start_date, 'date', false);
                                            if ($endCarbon) {
                                                 $progressLabel .= ' - '.App\Helpers\Helper::getFormattedDateObject($maintenance->expected_completion_date, 'date', false);;
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

                    <x-tabs.pane name="notes">
                        <x-table.history
                            :table_header="trans('general.notes')"
                            :model="$maintenance"
                            :route="route('api.activity.index', ['item_id' => $maintenance->id, 'item_type' => 'maintenance', 'action_type' => 'note added'])"
                            :hide_fields="['id','action_type', 'item', 'changed', 'target','file','file_download','quantity','changed','serial','signature_file','log_meta']"
                        />
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
                        <x-button.note :item="$maintenance"/>
                        @if (! $maintenance->completed_at)
                            @can('update', $maintenance->asset)
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#completeMaintenanceModal" data-tooltip="true" title="{{ trans('admin/maintenances/form.mark_complete') }}">
                                    <x-icon type="checkmark" class="fa-fw"/>
                                    <span class="sr-only">{{ trans('admin/maintenances/form.mark_complete') }}</span>
                                </button>
                            @endcan
                        @else
                            <span class="btn btn-sm btn-default disabled" data-tooltip="true" title="{{ trans('admin/maintenances/form.already_complete') }}: {{ Helper::getFormattedDateObject($maintenance->completed_at, 'datetime', false) }}">
                                <x-icon type="checkmark" class="fa-fw"/>
                                <span class="sr-only">{{ trans('admin/maintenances/form.already_complete') }}</span>
                            </span>
                        @endif
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
    @can('journal', $maintenance)
        @include ('modals.add-note', ['type' => 'maintenance', 'id' => $maintenance->id])
    @endcan

    <div class="modal fade" id="completeMaintenanceModal" tabindex="-1" role="dialog" aria-labelledby="completeMaintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('button.close') }}">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="completeMaintenanceModalLabel">{{ trans('admin/maintenances/form.mark_complete') }}</h4>
                </div>
                <form method="POST" action="{{ route('maintenances.complete', $maintenance->id) }}">
                    @csrf
                    <div class="modal-body">
                        <p>{{ trans('admin/maintenances/message.complete.confirm') }}</p>
                        <div class="form-group">
                            <label for="completionNote">{{ trans('admin/maintenances/form.completion_notes') }}</label>
                            <textarea class="form-control" id="completionNote" name="note" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                        <button type="submit" class="btn btn-success pull-right">{{ trans('admin/maintenances/form.mark_complete') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include ('partials.bootstrap-table')
@endsection

