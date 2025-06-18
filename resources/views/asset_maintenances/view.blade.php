<?php
use Carbon\Carbon;
?>
@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/asset_maintenances/general.view') }} {{ $assetMaintenance->title }}
@parent
@stop

@section('header_right')
  <div class="pull-right">
    <a href="{{ route('maintenances.edit', $assetMaintenance) }}" class="btn btn-default pull-right">
      {{ trans('general.update') }}</a>

    <a href="{{ route('maintenances.index') }}" class="btn btn-primary text-right" style="margin-right: 10px;">{{ trans('general.back') }}</a>
  </div>
@stop


{{-- Page content --}}
@section('content')
  <div class="row">
    <div class="col-md-9">

      <div class="box box-default">
        <div class="box-body">
          <div class="row-new-striped">
            <div class="row">

                <div class="col-md-3">
                  {{ trans('admin/asset_maintenances/form.asset_maintenance_type') }}
                </div>
                <div class="col-md-9">
                  {{ $assetMaintenance->asset_maintenance_type }}
                </div>

            </div> <!-- /row -->

            <div class="row">
              <div class="col-md-3">
                {{ trans('general.asset') }}
              </div>
              <div class="col-md-9">
                <a href="{{ route('hardware.show', $assetMaintenance->asset_id) }}">
                  {{ $assetMaintenance->asset->present()->fullName }}
                </a>
              </div>
            </div> <!-- /row -->

            @if ($assetMaintenance->asset->model)
              <div class="row">
                <div class="col-md-3">
                  {{ trans('general.asset_model') }}
                </div>
                <div class="col-md-9">
                  <a href="{{ route('models.show', $assetMaintenance->asset->model_id) }}">
                    {{ $assetMaintenance->asset->model->name }}
                  </a>
                </div>
              </div> <!-- /row -->
            @endif

            @if ($assetMaintenance->asset->company)
              <div class="row">
                <div class="col-md-3">
                  {{ trans('general.company') }}
                </div>
                <div class="col-md-9">
                  <a href="{{ route('companies.show', $assetMaintenance->asset->company_id) }}">
                    {{ $assetMaintenance->asset->company->name }}
                  </a>
                </div>
              </div> <!-- /row -->
            @endif


            @if ($assetMaintenance->supplier)
            <div class="row">
              <div class="col-md-3">
                {{ trans('general.supplier') }}
              </div>
              <div class="col-md-9">
                <a href="{{ route('suppliers.show', $assetMaintenance->supplier_id) }}">
                  {{ $assetMaintenance->supplier->name }}
                </a>
              </div>
            </div> <!-- /row -->
            @endif

            <div class="row">
              <div class="col-md-3">
                {{ trans('admin/asset_maintenances/form.start_date') }}
              </div>
              <div class="col-md-9">
                {{ Helper::getFormattedDateObject($assetMaintenance->start_date, 'date', false) }}
              </div>
            </div> <!-- /row -->

            <div class="row">
              <div class="col-md-3">
                {{ trans('admin/asset_maintenances/form.completion_date') }}
              </div>
              <div class="col-md-9">
                @if ($assetMaintenance->completion_date)
                  {{ Helper::getFormattedDateObject($assetMaintenance->completion_date, 'date', false) }}
                @else
                  {{ trans('admin/asset_maintenances/message.asset_maintenance_incomplete') }}
                @endif
              </div>
            </div> <!-- /row -->

            <div class="row">
              <div class="col-md-3">
                {{ trans('admin/asset_maintenances/form.asset_maintenance_time') }}
              </div>
              <div class="col-md-9">
                {{ $assetMaintenance->asset_maintenance_time }}
              </div>
            </div> <!-- /row -->

            @if ($assetMaintenance->cost > 0)
            <div class="row">
              <div class="col-md-3">
                {{ trans('admin/asset_maintenances/form.cost') }}
              </div>
              <div class="col-md-9">
                {{ \App\Models\Setting::getSettings()->default_currency .' '. Helper::formatCurrencyOutput($assetMaintenance->cost) }}
              </div>
            </div> <!-- /row -->
            @endif

            <div class="row">
              <div class="col-md-3">
                {{ trans('admin/asset_maintenances/form.is_warranty') }}
              </div>
              <div class="col-md-9">
                {{ $assetMaintenance->is_warranty ? trans('admin/asset_maintenances/message.warranty') : trans('admin/asset_maintenances/message.not_warranty') }}
              </div>
            </div> <!-- /row -->

            @if ($assetMaintenance->notes)
            <div class="row">
              <div class="col-md-3">
                {{ trans('admin/asset_maintenances/form.notes') }}
              </div>
              <div class="col-md-9">
                {!! nl2br(Helper::parseEscapedMarkedownInline($assetMaintenance->notes)) !!}
              </div>
            </div> <!-- /row -->
            @endif


          </div><!-- /row-new-striped -->
      </div><!-- /box-body -->
    </div><!-- /box -->

    </div> <!-- col-md-9  end -->
  </div> <!-- row  end -->

@stop
